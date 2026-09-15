<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\Concerns\DispatchesMosaicast;
use ArtisanToolbox\Mosaicast\Events\BroadcastMosaicastEvent;
use ArtisanToolbox\Mosaicast\Mosaicast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Event;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class EventWithPublicPayload
{
    public string $broadcastQueue = 'events';

    public ?string $socket = null;

    private string $secret = 'must-not-leak';

    public function __construct(public int $orderId) {}
}

final class EventWithBroadcastContract
{
    public function broadcastAs(): string
    {
        return 'orders.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['orderId' => 123, 'socket' => 'must-not-leak'];
    }
}

final class EventWithMosaicastShortcut
{
    use DispatchesMosaicast;

    public function __construct(public int $orderId) {}
}

enum EventBroadcastName: string
{
    case OrderUpdated = 'orders.updated';
}

final class EventWithEnumBroadcastName
{
    public function broadcastAs(): EventBroadcastName
    {
        return EventBroadcastName::OrderUpdated;
    }
}

function mosaicastRequest(): Request
{
    $session = new Store('test', new ArraySessionHandler(120));
    $session->setId(str_repeat('a', 40));
    $request = Request::create('/orders', 'POST');
    $request->setLaravelSession($session);

    return $request;
}

it('returns events through resolved Inertia props without requiring middleware', function (): void {
    $request = mosaicastRequest();
    app()->instance('request', $request);

    resolve(Mosaicast::class)->dispatch('orders.updated', ['orderId' => 123]);

    $payload = Inertia::getShared('mosaicast')();

    expect($payload)->toMatchArray([
        'events' => [[
            'name' => 'orders.updated',
            'payload' => ['orderId' => 123],
        ]],
    ])->and($payload['sessionIdentifier'])->toMatch('/^[a-f0-9]{64}$/');
});

it('registers the Mosaicast shared prop when Inertia is installed', function (): void {
    expect(Inertia::getShared('mosaicast'))->toBeCallable();
});

it('includes the session identifier on partial Inertia responses while leaving events unresolved', function (): void {
    $dispatched = [];
    Event::listen(BroadcastMosaicastEvent::class, function (BroadcastMosaicastEvent $event) use (&$dispatched): void {
        $dispatched[] = $event;
    });
    $request = mosaicastRequest();
    $request->headers->set('X-Inertia', 'true');
    $request->headers->set('X-Inertia-Partial-Component', 'Orders');
    $request->headers->set('X-Inertia-Partial-Data', 'orders');
    app()->instance('request', $request);

    resolve(Mosaicast::class)->dispatch('orders.updated', ['orderId' => 123]);
    $response = Inertia::render('Orders', ['orders' => []])->toResponse($request);
    $props = $response->getData(true)['props'];

    expect($props)->toHaveKey('mosaicastSessionIdentifier')
        ->not->toHaveKey('mosaicast')
        ->and($props['mosaicastSessionIdentifier'])->toMatch('/^[a-f0-9]{64}$/');

    event(new RequestHandled($request, $response));

    expect($dispatched)->toHaveCount(1)
        ->and($dispatched[0]->broadcastAs())->toBe('orders.updated');
});

it('uses a Laravel event class name and public properties by default', function (): void {
    $request = mosaicastRequest();
    app()->instance('request', $request);

    resolve(Mosaicast::class)->dispatch(new EventWithPublicPayload(123));

    $payload = Inertia::getShared('mosaicast')();

    expect($payload['events'])->toBe([[
        'name' => EventWithPublicPayload::class,
        'payload' => ['orderId' => 123],
    ]]);
});

it('uses Laravel broadcast naming and payload methods for event objects', function (): void {
    $request = mosaicastRequest();
    app()->instance('request', $request);

    resolve(Mosaicast::class)->dispatch(new EventWithBroadcastContract);

    $payload = Inertia::getShared('mosaicast')();

    expect($payload['events'])->toBe([[
        'name' => 'orders.updated',
        'payload' => ['orderId' => 123],
    ]]);
});

it('uses the value of a string-backed broadcast name enum', function (): void {
    $request = mosaicastRequest();
    app()->instance('request', $request);

    resolve(Mosaicast::class)->dispatch(new EventWithEnumBroadcastName);

    $payload = Inertia::getShared('mosaicast')();

    expect($payload['events'])->toBe([[
        'name' => 'orders.updated',
        'payload' => [],
    ]]);
});

it('dispatches an event through the static Mosaicast shortcut', function (): void {
    $request = mosaicastRequest();
    app()->instance('request', $request);

    EventWithMosaicastShortcut::mosaicast(123);

    $payload = Inertia::getShared('mosaicast')();

    expect($payload['events'])->toBe([[
        'name' => EventWithMosaicastShortcut::class,
        'payload' => ['orderId' => 123],
    ]]);
});

it('dispatches broadcasts for events that were not resolved through an Inertia response', function (): void {
    $dispatched = [];
    Event::listen(BroadcastMosaicastEvent::class, function (BroadcastMosaicastEvent $event) use (&$dispatched): void {
        $dispatched[] = $event;
    });
    $request = mosaicastRequest();
    app()->instance('request', $request);

    resolve(Mosaicast::class)->dispatch('orders.updated', ['orderId' => 123]);

    event(new RequestHandled($request, new Response));

    expect($dispatched)->toHaveCount(1)
        ->and($dispatched[0]->broadcastAs())->toBe('orders.updated')
        ->and($dispatched[0]->broadcastWith())->toBe(['orderId' => 123])
        ->and($dispatched[0]->broadcastOn())->toBeInstanceOf(PrivateChannel::class)
        ->and($dispatched[0]->broadcastOn()->name)->toBe('private-mosaicast.sessions.9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681');
});

it('delivers only unresolved events by broadcast after shared props are consumed', function (): void {
    $dispatched = [];
    Event::listen(BroadcastMosaicastEvent::class, function (BroadcastMosaicastEvent $event) use (&$dispatched): void {
        $dispatched[] = $event;
    });
    $request = mosaicastRequest();
    app()->instance('request', $request);
    $mosaicast = resolve(Mosaicast::class);

    $mosaicast->dispatch('orders.first', ['orderId' => 123]);
    $payload = Inertia::getShared('mosaicast')();
    $mosaicast->dispatch('orders.second', ['orderId' => 456]);

    event(new RequestHandled($request, new Response));

    expect($payload['events'])->toBe([[
        'name' => 'orders.first',
        'payload' => ['orderId' => 123],
    ]])->and($dispatched)->toHaveCount(1)
        ->and($dispatched[0]->broadcastAs())->toBe('orders.second')
        ->and($dispatched[0]->broadcastWith())->toBe(['orderId' => 456]);
});

it('requires an active session for implicit dispatch', function (): void {
    app()->instance('request', Request::create('/orders', 'POST'));

    resolve(Mosaicast::class)->dispatch('orders.updated', ['orderId' => 123]);
})->throws(LogicException::class, 'Mosaicast events require an active session request.');

it('broadcasts events emitted after the response was handled', function (): void {
    $dispatched = [];
    Event::listen(BroadcastMosaicastEvent::class, function (BroadcastMosaicastEvent $event) use (&$dispatched): void {
        $dispatched[] = $event;
    });
    $request = mosaicastRequest();
    app()->instance('request', $request);

    event(new RequestHandled($request, new Response));

    resolve(Mosaicast::class)->dispatch('orders.updated', ['orderId' => 123]);

    expect($dispatched)->toHaveCount(1)
        ->and($dispatched[0]->broadcastAs())->toBe('orders.updated')
        ->and($dispatched[0]->broadcastWith())->toBe(['orderId' => 123]);
});

it('broadcasts directly to an explicit session identifier', function (): void {
    $dispatched = [];
    Event::listen(BroadcastMosaicastEvent::class, function (BroadcastMosaicastEvent $event) use (&$dispatched): void {
        $dispatched[] = $event;
    });

    resolve(Mosaicast::class)
        ->toSession('9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681')
        ->dispatch('orders.updated', ['orderId' => 123]);

    expect($dispatched)->toHaveCount(1)
        ->and($dispatched[0]->broadcastAs())->toBe('orders.updated')
        ->and($dispatched[0]->broadcastWith())->toBe(['orderId' => 123])
        ->and($dispatched[0]->broadcastOn()->name)->toBe('private-mosaicast.sessions.9c9206cc5d38f284d87ca2cb6ad30b6c63a948b9960773742634793ed99f2681');
});

it('rejects malformed explicit session identifiers', function (): void {
    resolve(Mosaicast::class)->toSession('not-an-opaque-session-identifier');
})->throws(InvalidArgumentException::class, 'The session identifier must be a 64-character lowercase SHA-256 hash.');

it('uses the regenerated session identifier for events and shared props', function (): void {
    $request = mosaicastRequest();
    app()->instance('request', $request);
    $mosaicast = resolve(Mosaicast::class);

    $mosaicast->dispatch('orders.updated', ['orderId' => 123]);
    $originalIdentifier = $mosaicast->currentSessionIdentifier();

    $request->session()->setId(str_repeat('b', 40));
    $mosaicast->dispatch('orders.updated', ['orderId' => 456]);

    $payload = Inertia::getShared('mosaicast')();

    expect($payload['sessionIdentifier'])->not->toBe($originalIdentifier)
        ->and($payload['sessionIdentifier'])->toBe($mosaicast->currentSessionIdentifier())
        ->and($payload['events'])->toHaveCount(2);
});

it('uses the regenerated session identifier for a broadcast fallback', function (): void {
    $dispatched = [];
    Event::listen(BroadcastMosaicastEvent::class, function (BroadcastMosaicastEvent $event) use (&$dispatched): void {
        $dispatched[] = $event;
    });
    $request = mosaicastRequest();
    app()->instance('request', $request);

    resolve(Mosaicast::class)->dispatch('orders.updated', ['orderId' => 123]);
    $request->session()->setId(str_repeat('b', 40));

    event(new RequestHandled($request, new Response));

    expect($dispatched)->toHaveCount(1)
        ->and($dispatched[0]->broadcastOn()->name)
        ->toBe('private-mosaicast.sessions.84367bb1439cbe1e5018b7075eb748e952038ce1cee777ce4d20b0f60f657558');
});

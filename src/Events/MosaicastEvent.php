<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Events;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;

use function Illuminate\Support\enum_value;

final readonly class MosaicastEvent
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $name,
        public array $payload = [],
    ) {
        throw_if($this->name === '', InvalidArgumentException::class, 'The Mosaicast event name must not be empty.');
    }

    /**
     * Build an event envelope from Mosaicast's string API or a Laravel event object.
     *
     * Objects follow Laravel broadcasting conventions: `broadcastAs()` controls the
     * name, `broadcastWith()` controls the payload, and public properties are used
     * when no payload method is defined.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function from(string|object $event, array $payload = []): self
    {
        if (is_string($event)) {
            return new self($event, $payload);
        }

        throw_if($payload !== [], InvalidArgumentException::class, 'An event object defines its own payload and cannot receive a separate payload argument.');

        $name = method_exists($event, 'broadcastAs')
            ? enum_value($event->broadcastAs())
            : $event::class;

        throw_unless(is_string($name), InvalidArgumentException::class, 'The broadcast event name must be a string.');

        return new self($name, self::payloadFrom($event));
    }

    /**
     * @return array<string, mixed>
     */
    private static function payloadFrom(object $event): array
    {
        if (method_exists($event, 'broadcastWith') && ($payload = $event->broadcastWith()) !== null) {
            throw_unless(is_array($payload), InvalidArgumentException::class, 'The broadcast event payload must be an array.');

            unset($payload['socket']);

            return $payload;
        }

        $payload = [];

        foreach ((new ReflectionClass($event))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $payload[$property->getName()] = self::formatProperty($property->getValue($event));
        }

        unset($payload['broadcastQueue'], $payload['socket']);

        return $payload;
    }

    private static function formatProperty(mixed $value): mixed
    {
        return $value instanceof Arrayable ? $value->toArray() : $value;
    }

    /**
     * @return array{name: string, payload: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'payload' => $this->payload,
        ];
    }
}

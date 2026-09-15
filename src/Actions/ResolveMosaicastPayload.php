<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Actions;

use ArtisanToolbox\Mosaicast\Http\MosaicastRequestContext;
use Illuminate\Http\Request;

final readonly class ResolveMosaicastPayload
{
    public function __construct(private MosaicastRequestContext $context) {}

    /**
     * @return array{sessionIdentifier: string|null, events: list<array{name: string, payload: array<string, mixed>}>}
     */
    public function handle(Request $request): array
    {
        return [
            'sessionIdentifier' => $this->sessionIdentifier($request),
            'events' => $this->context->pullForInertia(),
        ];
    }

    public function sessionIdentifier(Request $request): ?string
    {
        $this->context->initialize($request);

        return $this->context->sessionIdentifier();
    }
}

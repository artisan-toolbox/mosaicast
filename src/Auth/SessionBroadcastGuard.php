<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Auth;

use ArtisanToolbox\Mosaicast\Broadcasting\SessionBroadcastChannel;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

final class SessionBroadcastGuard implements Guard
{
    private ?Authenticatable $user = null;

    private ?string $sessionId = null;

    private ?string $sessionToken = null;

    public function __construct(
        private Request $request,
        private readonly SessionBroadcastChannel $channel,
    ) {}

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?Authenticatable
    {
        $session = $this->request->hasSession() ? $this->request->session() : null;
        $sessionId = $session?->getId();
        $sessionToken = $session?->token();

        if ($sessionId === $this->sessionId && $sessionToken === $this->sessionToken) {
            return $this->user;
        }

        $this->sessionId = $sessionId;
        $this->sessionToken = $sessionToken;

        if ($sessionId === null || $sessionId === '' || $sessionToken === null || $sessionToken === '') {
            return $this->user = null;
        }

        return $this->user = new GenericUser([
            'id' => $this->channel->identifier($sessionId, $sessionToken),
            'password' => '',
            'remember_token' => null,
        ]);
    }

    public function id(): int|string|null
    {
        return $this->user()?->getAuthIdentifier();
    }

    /**
     * This guard authenticates possession of a session cookie, not credentials.
     *
     * @param  array<string, mixed>  $credentials
     */
    public function validate(array $credentials = []): bool
    {
        return false;
    }

    public function hasUser(): bool
    {
        return $this->user !== null && $this->user() !== null;
    }

    public function setUser(Authenticatable $user): static
    {
        $this->user();
        $this->user = $user;

        return $this;
    }

    public function setRequest(Request $request): static
    {
        $this->request = $request;
        $this->user = null;
        $this->sessionId = null;
        $this->sessionToken = null;

        return $this;
    }
}

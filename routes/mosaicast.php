<?php

declare(strict_types=1);

use ArtisanToolbox\Mosaicast\Broadcasting\SessionBroadcastChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    (string) config('mosaicast.broadcasting.session_channel_prefix').'.{sessionIdentifier}',
    SessionBroadcastChannel::class,
    ['guards' => [(string) config('mosaicast.broadcasting.guard')]],
);

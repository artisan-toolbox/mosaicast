<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Placeholder Artisan command shipped by the package mosaicast.')]
#[Signature('mosaicast:placeholder')]
class MosaicastCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Mosaicast placeholder command executed.');

        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast\Console\Commands;

use Illuminate\Console\Command;

class MosaicastCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'mosaicast:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package mosaicast.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Mosaicast placeholder command executed.');

        return self::SUCCESS;
    }
}

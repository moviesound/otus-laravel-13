<?php

namespace App\Console\Commands;


namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('cache:refresh')]
#[Description('Clear and warm up application cache')]
class RefreshCacheCommand extends Command
{
    public function handle(): int
    {
        $this->info('Clearing cache...');

        $result = $this->call('cache:clear');

        if ($result !== self::SUCCESS) {
            $this->error('Cache clear failed');

            return self::FAILURE;
        }

        $this->info('Cache warming...');

        $result = $this->call('cache:warmup');

        if ($result !== self::SUCCESS) {
            $this->error('Cache warmup failed');

            return self::FAILURE;
        }

        $this->info('Cache refreshed successfully');

        return self::SUCCESS;
    }
}

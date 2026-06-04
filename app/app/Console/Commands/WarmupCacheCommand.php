<?php

namespace App\Console\Commands;

use App\Models\Bot\SysText;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('cache:warmup
    {tableNames?* : Tables to warm up}
    {--c|check : Check cache is full}')]
#[Description('Warmup cache of application'
)]
class WarmupCacheCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $requestedTables = $this->argument('tableNames');
        $allowedTables = config('cache.warming_tables', []);

        if (empty($allowedTables)) {
            $this->error('No warming tables configured in cache.warming_tables');
            return self::FAILURE;
        }

        $tablesToWarm = empty($requestedTables)
            ? $allowedTables
            : $requestedTables;

        $tablesToWarm = array_values(array_intersect(
            $tablesToWarm,
            $allowedTables
        ));

        if (empty($tablesToWarm)) {
            $this->warn('No valid tables to warm up');
            return self::SUCCESS;
        }

        if ($this->option('check')) {
            $this->warn('Checking fullness of cache:');
        } else {
            $this->warn('Warming up cache:');
        }

        $rows = [];

        $bar = $this->output->createProgressBar(count($tablesToWarm));
        $bar->start();

        foreach ($tablesToWarm as $table) {

            $dbCount = $this->getDbCount($table);

            if ($this->option('check')) {
                $cacheCount = $this->getCacheCount($table);
            } else {
                $cacheCount = $this->warmupTable($table);
            }

            $rows[] = [
                'table' => $table,
                'db' => $dbCount,
                'cache' => $cacheCount,
            ];

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Table', 'DB Rows', 'Cache Rows'],
            $rows
        );

        return self::SUCCESS;
    }

    private function getDbCount(string $table): int
    {
        return match ($table) {
            'sys_texts' => SysText::count(),
            default => 0,
        };
    }

    private function getCacheCount(string $table): int
    {
        return match ($table) {
            'sys_texts' => $this->countSysTextCache(),
            default => 0,
        };
    }

    private function countSysTextCache(): int
    {
        $count = 0;

        SysText::query()
            ->select(['id', 'alias', 'lang'])
            ->chunkById(1000, function ($rows) use (&$count) {

                foreach ($rows as $row) {

                    if (Cache::tags(['sys_text'])->has(
                        "sys_text:{$row->lang}:{$row->alias}"
                    )) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    private function warmupTable(string $table): int
    {
        return match ($table) {
            'sys_texts' => $this->warmupSysText(),
            default => 0,
        };
    }

    private function warmupSysText(): int
    {
        $count = 0;

        SysText::query()
            ->select(['id', 'alias', 'lang', 'context'])
            ->chunkById(1000, function ($rows) use (&$count) {

                foreach ($rows as $row) {

                    Cache::tags(['sys_text'])->forever(
                        "sys_text:{$row->lang}:{$row->alias}",
                        [
                            'id' => $row->id,
                            'alias' => $row->alias,
                            'lang' => $row->lang,
                            'context' => $row->context,
                        ]
                    );

                    $count++;
                }
            });

        return $count;
    }
}

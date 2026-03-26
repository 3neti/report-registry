<?php

namespace LBHurtado\ReportRegistry\Console;

use Illuminate\Console\Command;
use LBHurtado\ReportRegistry\Services\ReportRegistry;

class ListReportsCommand extends Command
{
    protected $signature = 'report:list
                            {--group= : Filter by report group}';

    protected $description = 'List all available report drivers';

    public function handle(ReportRegistry $registry): int
    {
        $drivers = $registry->list();

        if (empty($drivers)) {
            $this->warn('No report drivers found.');

            return self::SUCCESS;
        }

        $group = $this->option('group');

        $rows = [];
        foreach ($drivers as $entry) {
            try {
                $driver = $registry->driver($entry['id'], $entry['version']);

                if ($group && $driver->group !== $group) {
                    continue;
                }

                $rows[] = [
                    $driver->id,
                    $driver->title,
                    $driver->group ?? '—',
                    $driver->version,
                    count($driver->columns).' cols',
                ];
            } catch (\Throwable $e) {
                $rows[] = [
                    $entry['id'],
                    '<error>Load error: '.$e->getMessage().'</error>',
                    '—',
                    $entry['version'],
                    '—',
                ];
            }
        }

        if (empty($rows)) {
            $this->warn("No reports found for group: {$group}");

            return self::SUCCESS;
        }

        $this->table(['ID', 'Title', 'Group', 'Version', 'Columns'], $rows);

        return self::SUCCESS;
    }
}

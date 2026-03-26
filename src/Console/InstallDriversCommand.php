<?php

namespace LBHurtado\ReportRegistry\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class InstallDriversCommand extends Command
{
    protected $signature = 'report:install-drivers
                            {--force : Overwrite existing drivers}';

    protected $description = 'Install report drivers from registered package sources to storage';

    public function handle(): int
    {
        $disk = config('report-registry.driver_disk', 'report-drivers');
        $sources = config('report-registry.driver_sources', []);

        if (empty($sources)) {
            $this->line('<comment>No driver sources registered.</comment>');

            return self::SUCCESS;
        }

        $force = $this->option('force');
        $installed = 0;
        $skipped = 0;

        foreach ($sources as $sourcePath) {
            if (! File::isDirectory($sourcePath)) {
                $this->warn("Source directory not found: {$sourcePath}");

                continue;
            }

            foreach (File::directories($sourcePath) as $dir) {
                $driverId = basename($dir);

                foreach (File::files($dir) as $file) {
                    $filename = $file->getFilename();
                    $targetPath = "{$driverId}/{$filename}";

                    if (Storage::disk($disk)->exists($targetPath) && ! $force) {
                        $this->line("<comment>Skipped:</comment> {$targetPath} (exists)");
                        $skipped++;

                        continue;
                    }

                    $content = File::get($file->getPathname());
                    Storage::disk($disk)->put($targetPath, $content);
                    $this->info("<info>Installed:</info> {$targetPath}");
                    $installed++;
                }
            }
        }

        $this->newLine();
        $this->info("Installed: {$installed}, Skipped: {$skipped}");

        return self::SUCCESS;
    }
}

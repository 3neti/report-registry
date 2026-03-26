<?php

namespace LBHurtado\ReportRegistry\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use LBHurtado\ReportRegistry\Services\ReportExecutor;

class RunReportCommand extends Command
{
    protected $signature = 'report:run
                            {driver : Report driver ID (e.g. vouchers.recent)}
                            {--format=text : Output format (json, csv, text, html)}
                            {--filter=* : Filters as key:value pairs}
                            {--sort= : Sort column}
                            {--sort-dir=desc : Sort direction (asc, desc)}
                            {--per-page=10 : Results per page}
                            {--page=1 : Page number}
                            {--user= : User email for auth context}
                            {--output= : Write output to file instead of stdout}';

    protected $description = 'Execute a report and output in the specified format';

    public function handle(ReportExecutor $executor): int
    {
        $this->authenticateUser();

        $filters = $this->parseFilters();
        $format = $this->option('format');
        $driverId = $this->argument('driver');

        try {
            $result = $executor->execute(
                driverId: $driverId,
                format: $format,
                filters: $filters,
                sort: $this->option('sort'),
                sortDirection: $this->option('sort-dir'),
                perPage: (int) $this->option('per-page'),
                page: (int) $this->option('page'),
            );

            $output = is_array($result) ? json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $result;

            if ($filePath = $this->option('output')) {
                file_put_contents($filePath, $output);
                $this->info("Report written to: {$filePath}");
            } else {
                $this->line($output);
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Report failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    protected function authenticateUser(): void
    {
        $email = $this->option('user');
        if (! $email) {
            return;
        }

        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('email', $email)->first();

        if (! $user) {
            $this->warn("User not found: {$email} — running without auth context");

            return;
        }

        Auth::login($user);
    }

    protected function parseFilters(): array
    {
        $filters = [];
        foreach ($this->option('filter') as $raw) {
            if (str_contains($raw, ':')) {
                [$key, $value] = explode(':', $raw, 2);
                $filters[$key] = $value;
            }
        }

        return $filters;
    }
}

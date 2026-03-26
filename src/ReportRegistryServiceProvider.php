<?php

namespace LBHurtado\ReportRegistry;

use Illuminate\Support\ServiceProvider;
use LBHurtado\ReportRegistry\Console\InstallDriversCommand;
use LBHurtado\ReportRegistry\Console\ListReportsCommand;
use LBHurtado\ReportRegistry\Console\RunReportCommand;
use LBHurtado\ReportRegistry\Formatters\CsvFormatter;
use LBHurtado\ReportRegistry\Formatters\HtmlFormatter;
use LBHurtado\ReportRegistry\Formatters\JsonFormatter;
use LBHurtado\ReportRegistry\Formatters\TextFormatter;
use LBHurtado\ReportRegistry\Services\HandlebarsEngine;
use LBHurtado\ReportRegistry\Services\ReportDriverService;
use LBHurtado\ReportRegistry\Services\ReportExecutor;
use LBHurtado\ReportRegistry\Services\ReportRegistry;

class ReportRegistryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/report-registry.php', 'report-registry');

        $this->app->singleton(HandlebarsEngine::class);

        $this->app->singleton(ReportDriverService::class, function ($app) {
            return new ReportDriverService(
                driverDisk: config('report-registry.driver_disk'),
            );
        });

        $this->app->singleton(ReportRegistry::class, function ($app) {
            $registry = new ReportRegistry(
                driverService: $app->make(ReportDriverService::class),
            );

            $this->registerBuiltInFormatters($registry, $app);

            return $registry;
        });

        $this->app->singleton(ReportExecutor::class, function ($app) {
            return new ReportExecutor(
                registry: $app->make(ReportRegistry::class),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallDriversCommand::class,
                ListReportsCommand::class,
                RunReportCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/report-registry.php' => config_path('report-registry.php'),
            ], 'report-registry-config');

            $this->publishes([
                __DIR__.'/../resources/templates' => resource_path('report-templates'),
            ], 'report-registry-templates');
        }
    }

    protected function registerBuiltInFormatters(ReportRegistry $registry, $app): void
    {
        $registry->registerFormatter('json', new JsonFormatter);
        $registry->registerFormatter('csv', new CsvFormatter);
        $registry->registerFormatter('text', new TextFormatter);
        $registry->registerFormatter('html', new HtmlFormatter($app->make(HandlebarsEngine::class)));
    }
}

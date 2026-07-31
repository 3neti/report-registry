<?php

use LBHurtado\ReportRegistry\Data\ReportDriverData;
use LBHurtado\ReportRegistry\Formatters\CsvFormatter;
use LBHurtado\ReportRegistry\Formatters\HtmlFormatter;
use LBHurtado\ReportRegistry\Formatters\JsonFormatter;
use LBHurtado\ReportRegistry\Formatters\TextFormatter;
use LBHurtado\ReportRegistry\Services\ReportDriverService;
use LBHurtado\ReportRegistry\Services\ReportRegistry;

it('registers the built in report formatters', function () {
    $registry = app(ReportRegistry::class);

    expect($registry->formatters())
        ->toBe(['json', 'csv', 'text', 'html'])
        ->and($registry->formatter('json'))->toBeInstanceOf(JsonFormatter::class)
        ->and($registry->formatter('csv'))->toBeInstanceOf(CsvFormatter::class)
        ->and($registry->formatter('text'))->toBeInstanceOf(TextFormatter::class)
        ->and($registry->formatter('html'))->toBeInstanceOf(HtmlFormatter::class);
});

it('loads the latest versioned report driver by default', function () {
    $driver = app(ReportRegistry::class)->driver('sales');

    expect($driver->id)->toBe('sales')
        ->and($driver->version)->toBe('1.1.0')
        ->and($driver->title)->toBe('Sales Report v1.1')
        ->and($driver->defaultSort)->toBe('created_at')
        ->and($driver->defaultSortDirection)->toBe('asc')
        ->and($driver->defaultPerPage)->toBe(50)
        ->and($driver->columns)->toHaveCount(3)
        ->and($driver->htmlTemplate)->toBe('sales.hbs');
});

it('loads flat report drivers when no versioned directory exists', function () {
    $driver = app(ReportRegistry::class)->driver('flat-report');

    expect($driver->id)->toBe('flat-report')
        ->and($driver->version)->toBe('1.0.0')
        ->and($driver->columns)->toHaveCount(1);
});

it('rebuilds report driver DTOs from cached array payloads', function () {
    $service = app(ReportDriverService::class);

    $first = $service->load('sales');
    $service->clearCache();

    $second = $service->load('sales');

    expect($first)->toBeInstanceOf(ReportDriverData::class)
        ->and($second)->toBeInstanceOf(ReportDriverData::class)
        ->and($second->id)->toBe('sales');
});

<?php

use LBHurtado\ReportRegistry\Services\ReportExecutor;

it('returns a canonical payload array for raw format', function () {
    $result = app(ReportExecutor::class)->execute(
        driverId: 'sales',
        format: 'raw',
        filters: ['status' => 'approved'],
    );

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['report', 'data', 'meta'])
        ->and($result['report'])->toMatchArray([
            'id' => 'sales',
            'title' => 'Sales Report v1.1',
            'description' => 'Base sales report',
            'group' => 'finance',
        ])
        ->and($result['data'])->toBeArray()
        ->and($result['data'][0])->toMatchArray([
            'reference' => 'INV-001',
            'amount' => 1250.5,
            'status' => 'approved',
        ])
        ->and($result['meta'])->toMatchArray([
            'filters' => ['status' => 'approved'],
            'sort' => 'created_at',
            'sort_direction' => 'asc',
            'per_page' => 50,
            'page' => 1,
            'total' => 1,
        ]);
});

it('executes a report and formats it as json', function () {
    $result = app(ReportExecutor::class)->execute(
        driverId: 'sales',
        format: 'json',
        filters: ['status' => 'approved'],
    );

    $decoded = json_decode($result, true);

    expect($result)->toBeString()
        ->and($decoded)->toBeArray()
        ->and($decoded['report'])->toMatchArray([
            'id' => 'sales',
            'title' => 'Sales Report v1.1',
            'description' => 'Base sales report',
            'group' => 'finance',
        ])
        ->and($decoded['report']['columns'])->toBeArray()
        ->and($decoded['report']['columns'])->toHaveCount(3)
        ->and($decoded['report']['columns'][0])->toMatchArray([
            'key' => 'reference',
            'label' => 'Reference',
            'type' => 'text',
            'sortable' => false,
        ])
        ->and($decoded['report']['columns'][1])->toMatchArray([
            'key' => 'amount',
            'label' => 'Gross Amount',
            'type' => 'text',
            'sortable' => false,
        ])
        ->and($decoded['report']['columns'][2])->toMatchArray([
            'key' => 'status',
            'label' => 'Status',
            'type' => 'text',
            'sortable' => false,
        ])
        ->and($decoded['report']['filters'])->toBeArray()
        ->and($decoded['data'])->toMatchArray([
            [
                'reference' => 'INV-001',
                'amount' => 1250.5,
                'status' => 'approved',
            ],
        ])
        ->and($decoded['meta'])->toMatchArray([
            'filters' => ['status' => 'approved'],
            'sort' => 'created_at',
            'sort_direction' => 'asc',
            'per_page' => 50,
            'page' => 1,
            'total' => 1,
        ]);
});

it('renders html output using the configured template', function () {
    $result = app(ReportExecutor::class)->execute(
        driverId: 'sales',
        format: 'html',
        filters: ['status' => 'approved'],
    );

    expect($result)
        ->toBeString()
        ->toContain('<h1>Sales Report v1.1</h1>')
        ->toContain('INV-001 | approved | ₱1,250.50');
});

it('renders csv output as a string', function () {
    $result = app(ReportExecutor::class)->execute(
        driverId: 'sales',
        format: 'csv',
        filters: ['status' => 'approved'],
    );

    expect($result)
        ->toBeString()
        ->toContain('Reference')
        ->toContain('Gross Amount')
        ->toContain('Status')
        ->toContain('INV-001');
});

it('renders text output as a string', function () {
    $result = app(ReportExecutor::class)->execute(
        driverId: 'sales',
        format: 'text',
        filters: ['status' => 'approved'],
    );

    expect($result)
        ->toBeString()
        ->toContain('Reference')
        ->toContain('Gross Amount')
        ->toContain('Status')
        ->toContain('INV-001');
});

it('returns raw resolved data without formatting from resolveData', function () {
    $result = app(ReportExecutor::class)->resolveData(
        driverId: 'sales',
        filters: ['status' => 'queued'],
    );

    expect($result)
        ->toHaveKeys(['data', 'meta'])
        ->and($result['data'][0]['status'])->toBe('queued')
        ->and($result['meta']['per_page'])->toBe(50);
});
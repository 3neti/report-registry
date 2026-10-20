<?php

it('lists installed report drivers', function () {
    $this->artisan('report:list')
        ->expectsOutputToContain('Sales Report v1.1')
        ->assertSuccessful();
});

it('runs a report from the command line', function () {
    $this->artisan('report:run', [
        'driver' => 'sales',
        '--format' => 'json',
        '--filter' => ['status:approved'],
    ])
        ->expectsOutputToContain('INV-001')
        ->assertSuccessful();
});

it('fails cleanly when a report driver cannot be loaded', function () {
    $this->artisan('report:run', [
        'driver' => 'missing-report',
        '--format' => 'json',
    ])
        ->expectsOutputToContain('Report failed')
        ->assertFailed();
});

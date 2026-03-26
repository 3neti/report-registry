<?php

namespace LBHurtado\ReportRegistry\Services;

use LBHurtado\ReportRegistry\Contracts\ReportFormatterInterface;
use LBHurtado\ReportRegistry\Data\ReportDriverData;

class ReportRegistry
{
    /** @var array<string, ReportFormatterInterface> */
    protected array $formatters = [];

    public function __construct(
        protected ReportDriverService $driverService,
    ) {}

    /**
     * Register a formatter by name.
     */
    public function registerFormatter(string $name, ReportFormatterInterface $formatter): void
    {
        $this->formatters[$name] = $formatter;
    }

    /**
     * Get a formatter by name.
     */
    public function formatter(string $name): ReportFormatterInterface
    {
        if (! isset($this->formatters[$name])) {
            throw new \RuntimeException("Unknown report format: {$name}");
        }

        return $this->formatters[$name];
    }

    /**
     * Get all registered formatter names.
     */
    public function formatters(): array
    {
        return array_keys($this->formatters);
    }

    /**
     * Load a report driver.
     */
    public function driver(string $id, ?string $version = null): ReportDriverData
    {
        return $this->driverService->load($id, $version);
    }

    /**
     * List all available report drivers.
     */
    public function list(): array
    {
        return $this->driverService->list();
    }

    /**
     * Get the driver service for advanced operations.
     */
    public function driverService(): ReportDriverService
    {
        return $this->driverService;
    }
}

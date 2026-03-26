<?php

namespace LBHurtado\ReportRegistry\Services;

use LBHurtado\ReportRegistry\Contracts\ReportResolverInterface;
use LBHurtado\ReportRegistry\Data\ReportDriverData;

class ReportExecutor
{
    public function __construct(
        protected ReportRegistry $registry,
    ) {}

    /**
     * Execute a report: load driver → resolve data → format output.
     */
    public function execute(
        string $driverId,
        ?string $version = null,
        string $format = 'json',
        array $filters = [],
        ?string $sort = null,
        ?string $sortDirection = null,
        ?int $perPage = null,
        int $page = 1,
    ): string|array {
        $driver = $this->registry->driver($driverId, $version);

        $resolver = $this->resolveResolver($driver);

        $result = $resolver->resolve(
            filters: $filters,
            sort: $sort ?? $driver->defaultSort,
            sortDirection: $sortDirection ?? $driver->defaultSortDirection,
            perPage: $perPage ?? $driver->defaultPerPage,
            page: $page,
        );

        $formatter = $this->registry->formatter($format);

        return $formatter->format($driver, $result['data'], $result['meta']);
    }

    /**
     * Execute and return raw data (no formatting).
     */
    public function resolveData(
        string $driverId,
        ?string $version = null,
        array $filters = [],
        ?string $sort = null,
        ?string $sortDirection = null,
        ?int $perPage = null,
        int $page = 1,
    ): array {
        $driver = $this->registry->driver($driverId, $version);
        $resolver = $this->resolveResolver($driver);

        return $resolver->resolve(
            filters: $filters,
            sort: $sort ?? $driver->defaultSort,
            sortDirection: $sortDirection ?? $driver->defaultSortDirection,
            perPage: $perPage ?? $driver->defaultPerPage,
            page: $page,
        );
    }

    protected function resolveResolver(ReportDriverData $driver): ReportResolverInterface
    {
        if (! $driver->resolver) {
            throw new \RuntimeException("No resolver defined for report: {$driver->id}");
        }

        $resolver = app($driver->resolver);

        if (! $resolver instanceof ReportResolverInterface) {
            throw new \RuntimeException("{$driver->resolver} must implement ReportResolverInterface");
        }

        return $resolver;
    }
}

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
     * Execute a report.
     *
     * Contract:
     * - raw  => structured PHP array
     * - json => rendered JSON string
     * - html => rendered HTML string
     * - csv  => rendered CSV string
     * - text => rendered text string
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
    ): array|string {
        $driver = $this->registry->driver($driverId, $version);

        $resolved = $this->resolveData(
            driverId: $driverId,
            version: $version,
            filters: $filters,
            sort: $sort,
            sortDirection: $sortDirection,
            perPage: $perPage,
            page: $page,
        );

        if ($format === 'raw') {
            return $this->buildPayload($driver, $resolved);
        }

        $formatter = $this->registry->formatter($format);

        return $formatter->format(
            $driver,
            $resolved['data'] ?? [],
            $resolved['meta'] ?? [],
        );
    }

    /**
     * Execute and return raw resolved data from the resolver only.
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

    protected function buildPayload(ReportDriverData $driver, array $resolved): array
    {
        return [
            'report' => [
                'id' => $driver->id,
                'title' => $driver->title,
                'description' => $driver->description,
                'group' => $driver->group,
                'columns' => array_map(fn ($col) => $col->toArray(), $driver->columns),
                'filters' => array_map(fn ($filter) => $filter->toArray(), $driver->filters),
            ],
            'data' => $resolved['data'] ?? [],
            'meta' => $resolved['meta'] ?? [],
        ];
    }
}
<?php

namespace LBHurtado\ReportRegistry\Contracts;

interface ReportResolverInterface
{
    /**
     * Execute the report query and return structured results.
     *
     * @return array{data: array, meta: array{total: int, page: int, per_page: int, last_page: int}}
     */
    public function resolve(
        array $filters = [],
        ?string $sort = null,
        string $sortDirection = 'desc',
        int $perPage = 10,
        int $page = 1,
    ): array;
}

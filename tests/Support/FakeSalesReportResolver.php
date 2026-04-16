<?php

namespace LBHurtado\ReportRegistry\Tests\Support;

use LBHurtado\ReportRegistry\Contracts\ReportResolverInterface;

class FakeSalesReportResolver implements ReportResolverInterface
{
    public function resolve(
        array $filters = [],
        ?string $sort = null,
        ?string $sortDirection = null,
        ?int $perPage = null,
        int $page = 1,
    ): array {
        return [
            'data' => [
                [
                    'reference' => 'INV-001',
                    'amount' => 1250.5,
                    'status' => $filters['status'] ?? 'paid',
                ],
            ],
            'meta' => [
                'filters' => $filters,
                'sort' => $sort,
                'sort_direction' => $sortDirection,
                'per_page' => $perPage,
                'page' => $page,
                'total' => 1,
            ],
        ];
    }
}

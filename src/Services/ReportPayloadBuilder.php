<?php

namespace LBHurtado\ReportRegistry\Services;

use LBHurtado\ReportRegistry\Data\ReportDriverData;

final class ReportPayloadBuilder
{
    /**
     * @param  array<int, mixed>  $data
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public function build(ReportDriverData $driver, array $data, array $meta): array
    {
        return [
            'report' => [
                'id' => $driver->id,
                'version' => $driver->version,
                'title' => $driver->title,
                'description' => $driver->description,
                'group' => $driver->group,
                'columns' => array_map(fn ($column): array => $column->toArray(), $driver->columns),
                'filters' => array_map(fn ($filter): array => $filter->toArray(), $driver->filters),
            ],
            'data' => $data,
            'meta' => $meta,
        ];
    }
}

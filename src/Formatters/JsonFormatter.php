<?php

namespace LBHurtado\ReportRegistry\Formatters;

use LBHurtado\ReportRegistry\Contracts\ReportFormatterInterface;
use LBHurtado\ReportRegistry\Data\ReportDriverData;

class JsonFormatter implements ReportFormatterInterface
{
    public function format(ReportDriverData $driver, array $data, array $meta): string|array
    {
        return [
            'report' => [
                'id' => $driver->id,
                'title' => $driver->title,
                'description' => $driver->description,
                'group' => $driver->group,
                'columns' => array_map(fn ($col) => $col->toArray(), $driver->columns),
                'filters' => array_map(fn ($f) => $f->toArray(), $driver->filters),
            ],
            'data' => $data,
            'meta' => $meta,
        ];
    }

    public function contentType(): string
    {
        return 'application/json';
    }

    public function extension(): string
    {
        return 'json';
    }
}

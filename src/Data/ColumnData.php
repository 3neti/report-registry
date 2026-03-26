<?php

namespace LBHurtado\ReportRegistry\Data;

use Spatie\LaravelData\Data;

class ColumnData extends Data
{
    public function __construct(
        public string $key,
        public string $label,
        public string $type = 'text',
        public bool $sortable = false,
    ) {}
}

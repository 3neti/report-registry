<?php

namespace LBHurtado\ReportRegistry\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class ReportDriverData extends Data
{
    /**
     * @param  ColumnData[]  $columns
     * @param  FilterData[]  $filters
     */
    public function __construct(
        public string $id,
        public string $version,
        public string $title,
        public ?string $description = null,
        public ?string $group = null,
        public ?string $icon = null,
        public ?string $resolver = null,
        #[DataCollectionOf(ColumnData::class)]
        public array $columns = [],
        #[DataCollectionOf(FilterData::class)]
        public array $filters = [],
        public ?string $defaultSort = null,
        public string $defaultSortDirection = 'desc',
        public int $defaultPerPage = 10,
        public ?string $htmlTemplate = null,
    ) {}
}

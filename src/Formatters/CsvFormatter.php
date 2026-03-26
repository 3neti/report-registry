<?php

namespace LBHurtado\ReportRegistry\Formatters;

use LBHurtado\ReportRegistry\Contracts\ReportFormatterInterface;
use LBHurtado\ReportRegistry\Data\ReportDriverData;

class CsvFormatter implements ReportFormatterInterface
{
    public function format(ReportDriverData $driver, array $data, array $meta): string
    {
        $output = fopen('php://temp', 'r+');

        // Header row from driver columns
        $headers = array_map(fn ($col) => $col->label, $driver->columns);
        fputcsv($output, $headers);

        // Data rows keyed by column definition
        $keys = array_map(fn ($col) => $col->key, $driver->columns);
        foreach ($data as $row) {
            $values = array_map(fn ($key) => $row[$key] ?? '', $keys);
            fputcsv($output, $values);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    public function contentType(): string
    {
        return 'text/csv';
    }

    public function extension(): string
    {
        return 'csv';
    }
}

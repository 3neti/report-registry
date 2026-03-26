<?php

namespace LBHurtado\ReportRegistry\Formatters;

use LBHurtado\ReportRegistry\Contracts\ReportFormatterInterface;
use LBHurtado\ReportRegistry\Data\ReportDriverData;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

class TextFormatter implements ReportFormatterInterface
{
    public function format(ReportDriverData $driver, array $data, array $meta): string
    {
        $output = new BufferedOutput;
        $table = new Table($output);

        // Headers from driver columns
        $table->setHeaders(array_map(fn ($col) => $col->label, $driver->columns));

        // Rows keyed by column definition
        $keys = array_map(fn ($col) => $col->key, $driver->columns);
        foreach ($data as $row) {
            $table->addRow(array_map(fn ($key) => $row[$key] ?? '', $keys));
        }

        $table->render();

        $text = $output->fetch();

        // Append pagination footer
        $page = $meta['page'] ?? 1;
        $lastPage = $meta['last_page'] ?? 1;
        $total = $meta['total'] ?? count($data);
        $text .= "\n Page {$page} of {$lastPage} ({$total} total records)\n";

        return $text;
    }

    public function contentType(): string
    {
        return 'text/plain';
    }

    public function extension(): string
    {
        return 'txt';
    }
}

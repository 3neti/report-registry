<?php

namespace LBHurtado\ReportRegistry\Contracts;

use LBHurtado\ReportRegistry\Data\ReportDriverData;

interface ReportFormatterInterface
{
    /**
     * Format report data into the target output.
     */
    public function format(ReportDriverData $driver, array $data, array $meta): string|array;

    /**
     * MIME content type for HTTP responses.
     */
    public function contentType(): string;

    /**
     * File extension for downloads.
     */
    public function extension(): string;
}

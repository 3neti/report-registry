<?php

namespace LBHurtado\ReportRegistry\Formatters;

use LBHurtado\ReportRegistry\Contracts\ReportFormatterInterface;
use LBHurtado\ReportRegistry\Data\ReportDriverData;
use LBHurtado\ReportRegistry\Services\ReportPayloadBuilder;

class JsonFormatter implements ReportFormatterInterface
{
    public function __construct(
        ?ReportPayloadBuilder $payloadBuilder = null,
    ) {
        $this->payloadBuilder = $payloadBuilder ?? new ReportPayloadBuilder;
    }

    protected ReportPayloadBuilder $payloadBuilder;

    public function format(ReportDriverData $driver, array $data, array $meta): string
    {
        return json_encode(
            $this->payloadBuilder->build($driver, $data, $meta),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
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

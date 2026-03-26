<?php

namespace LBHurtado\ReportRegistry\Formatters;

use LBHurtado\ReportRegistry\Contracts\ReportFormatterInterface;
use LBHurtado\ReportRegistry\Data\ReportDriverData;
use LBHurtado\ReportRegistry\Services\HandlebarsEngine;

class HtmlFormatter implements ReportFormatterInterface
{
    public function __construct(
        protected HandlebarsEngine $engine,
    ) {}

    public function format(ReportDriverData $driver, array $data, array $meta): string
    {
        $templatePath = $this->resolveTemplate($driver);

        $context = [
            'report' => [
                'id' => $driver->id,
                'title' => $driver->title,
                'description' => $driver->description,
                'group' => $driver->group,
            ],
            'columns' => array_map(fn ($col) => $col->toArray(), $driver->columns),
            'rows' => $data,
            'meta' => $meta,
            'generated_at' => now()->toIso8601String(),
        ];

        return $this->engine->render($templatePath, $context);
    }

    public function contentType(): string
    {
        return 'text/html';
    }

    public function extension(): string
    {
        return 'html';
    }

    protected function resolveTemplate(ReportDriverData $driver): string
    {
        // 1. Driver-specific template in host app
        if ($driver->htmlTemplate) {
            $hostPath = config('report-registry.template_path').'/'.$driver->htmlTemplate;
            if (file_exists($hostPath)) {
                return $hostPath;
            }
        }

        // 2. Host app default override
        $hostDefault = config('report-registry.template_path').'/default.hbs';
        if (file_exists($hostDefault)) {
            return $hostDefault;
        }

        // 3. Package built-in default
        return __DIR__.'/../../resources/templates/default.hbs';
    }
}

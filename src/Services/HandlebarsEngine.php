<?php

namespace LBHurtado\ReportRegistry\Services;

use LightnCandy\LightnCandy;

class HandlebarsEngine
{
    /**
     * Render a template file with data.
     */
    public function render(string $templatePath, array $data): string
    {
        if (! file_exists($templatePath)) {
            throw new \RuntimeException("Template file not found: {$templatePath}");
        }

        return $this->renderString(file_get_contents($templatePath), $data);
    }

    /**
     * Render a template string with data.
     */
    public function renderString(string $template, array $data): string
    {
        $phpStr = LightnCandy::compile($template, [
            'flags' => LightnCandy::FLAG_HANDLEBARS
                | LightnCandy::FLAG_ERROR_EXCEPTION
                | LightnCandy::FLAG_RUNTIMEPARTIAL,
            'helpers' => $this->getHelpers(),
        ]);

        $renderer = LightnCandy::prepare($phpStr);

        return $renderer($data);
    }

    protected function getHelpers(): array
    {
        return [
            'lookup' => function ($obj, $key) {
                if (is_array($obj) && array_key_exists($key, $obj)) {
                    return $obj[$key];
                }

                return '';
            },
            'eq' => function ($a, $b) {
                return $a == $b;
            },
            'gt' => function ($a, $b) {
                return $a > $b;
            },
            'lt' => function ($a, $b) {
                return $a < $b;
            },
            'formatCurrency' => function ($value) {
                if (! is_numeric($value)) {
                    return $value;
                }

                return '₱'.number_format((float) $value, 2);
            },
            'formatDate' => function ($value) {
                if (empty($value)) {
                    return '';
                }

                try {
                    return (new \DateTime($value))->format('M d, Y h:i A');
                } catch (\Exception) {
                    return $value;
                }
            },
        ];
    }
}

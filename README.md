# 3neti/report-registry

Report Registry and Execution Engine for Laravel

---

## Overview

`3neti/report-registry` is a Laravel package for defining, discovering, executing, and rendering reports using YAML-based drivers.

It provides:
- Driver-based report definitions (YAML)
- Resolver-driven data execution
- Pluggable output formatters (JSON, HTML, CSV, Text)
- Clean separation between data resolution and rendering

---

## Installation

```bash
composer require 3neti/report-registry:^1.1
```

Laravel discovers the package service provider automatically.

---

## Key Concepts

### Report Driver
A YAML file that defines:
- metadata (id, title, description)
- columns
- filters
- resolver class
- optional templates

### Resolver
A class that implements:

```php
ReportResolverInterface
```

Responsible for fetching and returning report data.

### Executor

`ReportExecutor` orchestrates:
- loading the driver
- executing the resolver
- returning either raw data or formatted output

---

## Output Modes

The executor supports two output categories:

### 1. Raw (machine-readable)
Returns structured PHP array:

```php
$result = $executor->execute('sales', format: 'raw');
```

Structure:

```php
[
  'report' => [...],
  'data' => [...],
  'meta' => [...]
]
```

---

### 2. Rendered (string output)

Formats:
- `json`
- `html`
- `csv`
- `text`

```php
$json = $executor->execute('sales', format: 'json');
$html = $executor->execute('sales', format: 'html');
```

All return **strings**.

---

## Example Usage

```php
$executor = app(ReportExecutor::class);

// Raw payload
$data = $executor->execute('sales', format: 'raw');

// JSON output
$json = $executor->execute('sales', format: 'json');

// HTML output
$html = $executor->execute('sales', format: 'html');
```

---

## Resolver Example

```php
class SalesReportResolver implements ReportResolverInterface
{
    public function resolve(
        array $filters = [],
        ?string $sort = null,
        string $sortDirection = 'desc',
        int $perPage = 10,
        int $page = 1,
    ): array {
        return [
            'data' => [
                [
                    'reference' => 'INV-001',
                    'amount' => 1250.50,
                    'status' => 'approved',
                ]
            ],
            'meta' => [
                'total' => 1,
                'page' => 1,
                'per_page' => $perPage,
            ],
        ];
    }
}
```

---

## CLI Usage

```bash
php artisan report:run sales --format=json
php artisan report:run sales --format=html
php artisan report:run sales --format=raw
```

Options:
- `--filter=status:approved`
- `--sort=created_at`
- `--sort-dir=asc`
- `--per-page=50`
- `--page=1`
- `--output=report.json`

---

## Formatter Contract

All formatters implement:

```php
ReportFormatterInterface
```

```php
public function format(ReportDriverData $driver, array $data, array $meta): string;
```

Built-in formatters:
- JSON
- HTML
- CSV
- Text

---

## Driver Structure (YAML)

```yaml
driver:
  id: sales
  version: 1.1.0
  title: Sales Report

columns:
  - key: reference
    label: Reference
  - key: amount
    label: Amount

resolver: App\Reports\SalesReportResolver
```

---

## Design Principles

- **Separation of concerns**
  - Resolver = data
  - Formatter = output
- **Driver-driven architecture**
- **Extensible format system**
- **API-first execution model**

---

## Testing

The current release matrix covers PHP 8.3 and 8.4 on Laravel 12
and 13. Laravel 11 remains supported by the package constraints.

Run tests:

```bash
composer test
```

Coverage includes:
- driver loading
- version resolution
- execution
- formatting
- output contracts

---

## License

MIT

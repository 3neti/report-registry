# Resolvers

Resolvers implement `ReportResolverInterface` and are the domain-owned part of
a report. A resolver receives filters, sorting, and pagination and returns:

```php
[
    'data' => [...],
    'meta' => [
        'total' => 1,
        'page' => 1,
        'per_page' => 50,
    ],
]
```

The registry does not know how to calculate totals, authorize rows, classify
money, or query models. Those rules belong to the package that owns the
resolver. Keep amounts in the domain's canonical unit and expose presentation
metadata separately.

Resolvers should be deterministic for the same filters and should use stable
sort columns. They may add domain-specific metadata, which is preserved by all
formatters.

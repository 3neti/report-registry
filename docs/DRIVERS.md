# Report drivers

A driver is a versioned YAML definition that describes a report without owning
its data access. Drivers live in a package source directory and are installed
onto the configured `report-drivers` disk.

## Minimal driver

```yaml
driver:
  id: sales
  version: 1.0.0
  title: Sales
  group: finance

resolver: App\\Reports\\SalesReportResolver

columns:
  - key: reference
    label: Reference
    type: text
    sortable: true
```

Versioned drivers use `sales/v1.0.0.yaml`. When no version is requested, the
highest semantic version is selected. A flat `sales.yaml` remains supported
for legacy drivers.

## Composition

Drivers can extend one or more drivers using `id@version` references:

```yaml
extends:
  - base-sales@1.0.0
```

The child definition wins for `driver`, `columns`, `filters`, `defaults`,
`resolver`, and `templates`. Circular inheritance is rejected.

## Filter and output metadata

Filters and columns are descriptive metadata. Resolvers remain responsible for
validating values, applying authorization, querying data, and returning the
canonical `data` and `meta` arrays.

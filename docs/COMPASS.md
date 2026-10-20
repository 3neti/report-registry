# Report Registry Compass

`report-registry` is reporting infrastructure. It discovers versioned driver
definitions, delegates data resolution to domain packages, and renders stable
output formats.

- Domain packages own queries, authorization, calculations, and financial truth.
- Drivers describe metadata; they do not perform accounting or ledger writes.
- Resolvers return deterministic data and metadata for a requested filter set.
- Formatters never change domain values.
- Raw JSON is the contract consumed by dashboards and integrations.
- Backward compatibility for flat drivers and existing resolver/formatter
  contracts is maintained until a documented major release.

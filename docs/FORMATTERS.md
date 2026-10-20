# Formatters and templates

Formatters implement `ReportFormatterInterface` and receive a driver, resolved
rows, and metadata. Built-in formats are `json`, `csv`, `text`, and `html`.

JSON is the canonical machine-readable representation. CSV and text use the
driver column order. HTML renders a Handlebars template and falls back to the
published host template, then the package default template.

Templates receive `report`, `columns`, `rows`, `meta`, and `generated_at`.
Built-in helpers include `lookup`, `eq`, `gt`, `lt`, `formatCurrency`, and
`formatDate`. Treat resolver data as untrusted output and keep secrets out of
the report payload.

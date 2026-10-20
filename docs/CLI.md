# Artisan commands

Install registered package drivers:

```shell
php artisan report:install-drivers
php artisan report:install-drivers --force
```

Inspect installed drivers:

```shell
php artisan report:list
php artisan report:list --group=finance
```

Run a report:

```shell
php artisan report:run sales --format=json
php artisan report:run sales --filter=status:approved --sort=created_at --sort-dir=asc
php artisan report:run sales --format=csv --output=/tmp/sales.csv
```

The optional `--user` argument establishes an application authentication
context for resolvers that use the current user. A resolver must still enforce
its own authorization rules.

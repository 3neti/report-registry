<?php

namespace LBHurtado\ReportRegistry\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use LBHurtado\ReportRegistry\Data\ColumnData;
use LBHurtado\ReportRegistry\Data\FilterData;
use LBHurtado\ReportRegistry\Data\ReportDriverData;
use Symfony\Component\Yaml\Yaml;

class ReportDriverService
{
    protected array $loaded = [];

    public function __construct(
        protected ?string $driverDisk = null,
    ) {
        $this->driverDisk = $driverDisk ?? config('report-registry.driver_disk');
    }

    protected function disk(): Filesystem
    {
        return Storage::disk($this->driverDisk);
    }

    /**
     * Load a driver by ID with optional version.
     */
    public function load(string $driverId, ?string $version = null): ReportDriverData
    {
        $cacheKey = "report_driver:{$driverId}:{$version}";

        if (isset($this->loaded[$cacheKey])) {
            return $this->loaded[$cacheKey];
        }

        $ttl = config('report-registry.cache_ttl', 3600);

        $driver = $ttl > 0
            ? Cache::remember($cacheKey, $ttl, fn () => $this->loadFromFile($driverId, $version))
            : $this->loadFromFile($driverId, $version);

        $this->loaded[$cacheKey] = $driver;

        return $driver;
    }

    protected function loadFromFile(string $driverId, ?string $version = null): ReportDriverData
    {
        $path = $this->resolveDriverPath($driverId, $version);
        $content = $this->disk()->get($path);
        $data = Yaml::parse($content);

        if (isset($data['extends'])) {
            $data = $this->resolveComposition($data, [$this->normalizeDriverRef($driverId, $version)]);
        }

        return $this->parseDriver($data, $driverId);
    }

    protected function resolveComposition(array $data, array $resolved = []): array
    {
        $extends = $data['extends'] ?? [];
        unset($data['extends']);

        if (empty($extends)) {
            return $data;
        }

        $merged = [];

        foreach ($extends as $parentRef) {
            [$parentId, $parentVersion] = $this->parseDriverRef($parentRef);
            $normalizedParentRef = $this->normalizeDriverRef($parentId, $parentVersion);

            if (in_array($normalizedParentRef, $resolved, true)) {
                throw new \RuntimeException('Circular dependency: '.implode(' -> ', [...$resolved, $normalizedParentRef]));
            }

            $parentPath = $this->resolveDriverPath($parentId, $parentVersion);
            $parentData = Yaml::parse($this->disk()->get($parentPath));

            if (isset($parentData['extends'])) {
                $parentData = $this->resolveComposition($parentData, [...$resolved, $normalizedParentRef]);
            }

            $merged = $this->mergeDrivers($merged, $parentData);
        }

        return $this->mergeDrivers($merged, $data);
    }

    protected function parseDriverRef(string $ref): array
    {
        if (str_contains($ref, '@')) {
            [$id, $version] = explode('@', $ref, 2);

            return [$id, $version];
        }

        return [$ref, null];
    }

    protected function normalizeDriverRef(string $driverId, ?string $version = null): string
    {
        return $version ? sprintf('%s@%s', $driverId, $version) : $driverId;
    }

    protected function mergeDrivers(array $base, array $overlay): array
    {
        if (empty($base)) {
            return $overlay;
        }

        $result = $base;

        if (isset($overlay['driver'])) {
            $result['driver'] = array_merge($result['driver'] ?? [], $overlay['driver']);
        }

        if (isset($overlay['columns'])) {
            $result['columns'] = $overlay['columns'];
        }

        if (isset($overlay['filters'])) {
            $result['filters'] = $overlay['filters'];
        }

        if (isset($overlay['defaults'])) {
            $result['defaults'] = array_merge($result['defaults'] ?? [], $overlay['defaults']);
        }

        if (isset($overlay['resolver'])) {
            $result['resolver'] = $overlay['resolver'];
        }

        if (isset($overlay['templates'])) {
            $result['templates'] = array_merge($result['templates'] ?? [], $overlay['templates']);
        }

        return $result;
    }

    protected function resolveDriverPath(string $driverId, ?string $version = null): string
    {
        if ($version) {
            $versionedPath = "{$driverId}/v{$version}.yaml";
            if ($this->disk()->exists($versionedPath)) {
                return $versionedPath;
            }
        }

        $files = $this->disk()->exists($driverId) ? $this->disk()->files($driverId) : [];
        $versionFiles = array_values(array_filter($files, fn ($f) => preg_match('/v[\d.]+\.yaml$/', basename($f))));
        if (! empty($versionFiles)) {
            usort($versionFiles, function (string $a, string $b): int {
                preg_match('/v([\d.]+)\.yaml$/', basename($a), $am);
                preg_match('/v([\d.]+)\.yaml$/', basename($b), $bm);

                return version_compare($am[1] ?? '0.0.0', $bm[1] ?? '0.0.0');
            });

            return end($versionFiles);
        }

        $flatPath = "{$driverId}.yaml";
        if ($this->disk()->exists($flatPath)) {
            return $flatPath;
        }

        throw new \RuntimeException("Report driver not found: {$driverId}");
    }

    protected function parseDriver(array $data, string $driverId): ReportDriverData
    {
        $driver = $data['driver'] ?? [];
        $defaults = $data['defaults'] ?? [];

        $columns = array_map(
            fn (array $col) => new ColumnData(
                key: $col['key'],
                label: $col['label'],
                type: $col['type'] ?? 'text',
                sortable: $col['sortable'] ?? false,
            ),
            $data['columns'] ?? [],
        );

        $filters = array_map(
            fn (array $filter) => new FilterData(
                key: $filter['key'],
                label: $filter['label'],
                type: $filter['type'] ?? 'text',
                options: $filter['options'] ?? null,
            ),
            $data['filters'] ?? [],
        );

        return new ReportDriverData(
            id: $driver['id'] ?? $driverId,
            version: $driver['version'] ?? '1.0.0',
            title: $driver['title'] ?? $driverId,
            description: $driver['description'] ?? null,
            group: $driver['group'] ?? null,
            icon: $driver['icon'] ?? null,
            resolver: $data['resolver'] ?? null,
            columns: $columns,
            filters: $filters,
            defaultSort: $defaults['sort'] ?? null,
            defaultSortDirection: $defaults['sort_direction'] ?? 'desc',
            defaultPerPage: $defaults['per_page'] ?? 10,
            htmlTemplate: $data['templates']['html'] ?? null,
        );
    }

    /**
     * List all available drivers on disk.
     */
    public function list(): array
    {
        $drivers = [];

        foreach ($this->disk()->directories() as $driverId) {
            foreach ($this->disk()->files($driverId) as $file) {
                if (preg_match('/v(.+)\.yaml$/', basename($file), $matches)) {
                    $drivers[] = ['id' => $driverId, 'version' => $matches[1], 'path' => $file];
                }
            }
        }

        foreach ($this->disk()->files() as $file) {
            if (str_ends_with($file, '.yaml')) {
                $drivers[] = ['id' => basename($file, '.yaml'), 'version' => '1.0.0', 'path' => $file];
            }
        }

        return $drivers;
    }

    public function clearCache(): void
    {
        $this->loaded = [];
    }
}
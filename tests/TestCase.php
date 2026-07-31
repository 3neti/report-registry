<?php

namespace LBHurtado\ReportRegistry\Tests;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use LBHurtado\ReportRegistry\ReportRegistryServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\Normalizers\ArrayableNormalizer;
use Spatie\LaravelData\Normalizers\ArrayNormalizer;
use Spatie\LaravelData\Normalizers\JsonNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;
use Spatie\LaravelData\Normalizers\ObjectNormalizer;

abstract class TestCase extends Orchestra
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('data.validation_strategy', 'always');
        $app['config']->set('data.max_transformation_depth', 6);
        $app['config']->set('data.throw_when_max_transformation_depth_reached', 6);
        $app['config']->set('data.normalizers', [
            ModelNormalizer::class,
            ArrayableNormalizer::class,
            ObjectNormalizer::class,
            ArrayNormalizer::class,
            JsonNormalizer::class,
        ]);
        $app['config']->set('data.date_format', 'Y-m-d\\TH:i:sP');

        $app['config']->set('filesystems.disks.report-drivers', [
            'driver' => 'local',
            'root' => __DIR__.'/Fixtures/report-drivers',
            'throw' => false,
        ]);

        $app['config']->set('report-registry.driver_disk', 'report-drivers');
        $app['config']->set('report-registry.cache_ttl', 0);
        $app['config']->set('report-registry.template_path', __DIR__.'/Fixtures/templates');
    }

    protected function getPackageProviders($app): array
    {
        return [
            ReportRegistryServiceProvider::class,
        ];
    }

    protected function reportDriverDisk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('report-drivers');

        return $disk;
    }
}

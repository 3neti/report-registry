<?php

namespace LBHurtado\ReportRegistry\Tests;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use LBHurtado\ReportRegistry\ReportRegistryServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('data.validation_strategy', 'always');
        $app['config']->set('data.max_transformation_depth', 6);
        $app['config']->set('data.throw_when_max_transformation_depth_reached', 6);
        $app['config']->set('data.normalizers', [
            \Spatie\LaravelData\Normalizers\ModelNormalizer::class,
            \Spatie\LaravelData\Normalizers\ArrayableNormalizer::class,
            \Spatie\LaravelData\Normalizers\ObjectNormalizer::class,
            \Spatie\LaravelData\Normalizers\ArrayNormalizer::class,
            \Spatie\LaravelData\Normalizers\JsonNormalizer::class,
        ]);
        $app['config']->set('data.date_format', "Y-m-d\\TH:i:sP");

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

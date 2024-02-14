<?php

namespace janole\Laravel\Dockerize;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__.'/../docker/dockerignore' => base_path('.dockerignore')]);

        $this->commands(
            [
                \janole\Laravel\Dockerize\Console\Commands\ContainerStartup::class,
                \janole\Laravel\Dockerize\Console\Commands\ContainerBuild::class,
                \janole\Laravel\Dockerize\Console\Commands\DockerBuild::class,
                \janole\Laravel\Dockerize\Console\Commands\DockerCompose::class,
            ]
        );
    }
}

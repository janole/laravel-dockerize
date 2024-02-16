<?php

namespace janole\Laravel\Dockerize;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands(
            [
                Console\Commands\ContainerStartup::class,
                Console\Commands\DockerBuild::class,
                Console\Commands\DockerRunImageBuildScripts::class,
                Console\Commands\DockerCompose::class,
            ]
        );
    }
}

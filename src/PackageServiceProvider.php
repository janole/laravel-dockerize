<?php

namespace janole\Laravel\Dockerize;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
		$this->commands([\janole\Laravel\Dockerize\Console\Commands\ContainerStartup::class]);

		$this->commands([\janole\Laravel\Dockerize\Console\Commands\DockerBuild::class]);

		$this->commands([\janole\Laravel\Dockerize\Console\Commands\DockerCompose::class]);
    }
}

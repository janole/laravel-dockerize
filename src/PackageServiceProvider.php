<?php

namespace janole\Laravel\Dockerize;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->mergeConfigFrom(__DIR__.'/../config/dockerize.php', 'dockerize');
	}

    public function boot()
    {
        $this->publishes([__DIR__.'/../config/dockerize.php' => config_path('dockerize.php')]);
        $this->publishes([__DIR__.'/../docker/dockerignore' => base_path('.dockerignore')]);

        $this->commands([\janole\Laravel\Dockerize\Console\Commands\ContainerStartup::class]);

        $this->commands([\janole\Laravel\Dockerize\Console\Commands\DockerBuild::class]);

        $this->commands([\janole\Laravel\Dockerize\Console\Commands\DockerCompose::class]);
    }
}

<?php

namespace janole\Laravel\Dockerize\Console\Commands;

use Illuminate\Console\Command;

class DockerBuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Docker Image of this Laravel App';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        self::build(false);
    }

    /**
     * ...
     *
     * @return mixed
     */
    public static function build($silent = false)
    {
        //
        $dockerignore = file_get_contents(base_path("vendor/janole/laravel-dockerize/docker/dockerignore"));
        file_put_contents(base_path(".dockerignore"), $dockerignore);

        //
        $IMAGE = "docker.rocket-es.net/abc";
        $dockerfile = base_path("vendor/janole/laravel-dockerize/docker/Dockerfile");

        //
        $cmd = "(cd " . base_path() . " && docker build -t $IMAGE -f $dockerfile .)";
        passthru($cmd);

    	return 0;
    }
}

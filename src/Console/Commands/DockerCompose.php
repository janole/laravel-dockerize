<?php

namespace janole\Laravel\Dockerize\Console\Commands;

use Illuminate\Console\Command;

class DockerCompose extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:compose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the Laravel App via Docker';

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
        self::compose(false);
    }

    /**
     * ...
     *
     * @return mixed
     */
    public static function compose($silent = false)
    {
        //
        $IMAGE = "docker.rocket-es.net/abc";
        $dockercompose = base_path("vendor/janole/laravel-dockerize/docker/docker-compose.yml");

        //
        $NAME = "abc";

        //
        $cmd = "cd " . base_path() . " && IMAGE=$IMAGE && PORT=3037 && docker-compose -f $dockercompose -p $NAME";
        echo "> $cmd\n";
        // passthru($cmd);

    	return 0;
    }
}

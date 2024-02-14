<?php

namespace janole\Laravel\Dockerize\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ContainerBuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'container:build {--run : Run all configured build scripts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all configured build scripts (artisan commands) ...';

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
     * @return int
     */
    public function handle(): int
    {
        if (!$this->option("run"))
        {
            $this->error("ERROR: Run only from Dockerfile");

            return -1;
        }

        return $this->runBuildScripts();
    }

    /**
     * Run all configured build scripts to clean-up the docker image.
     *
     * @return int
     */
    public function runBuildScripts(): int
    {
        $this->info("Execute build scripts for " . config("app.name") . "/" . env("DOCKERIZE_IMAGE") . ":" . env("DOCKERIZE_VERSION") . "-" . env("DOCKERIZE_BRANCH") . " ...");

        $artisan = json_decode(env("DOCKERIZE_BUILD_COMMANDS"), true) ?? [];

        foreach ($artisan as $command)
        {
            $this->info("Running $command.");

            if (Artisan::call($command))
            {
                $this->error(trim(Artisan::output()));
            }
        }

        $this->info("Finished.");

        return 0;
    }
}

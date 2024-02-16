<?php

namespace janole\Laravel\Dockerize\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use janole\Laravel\Dockerize\Services\DockerBuildImageService;

class DockerRunImageBuildScripts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:run-image-build-scripts {--run : Run all configured "docker build ..." scripts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run configured build scripts during "docker build ..."';

    /** @var string */
    private $name;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->name = config('app.name') . '/' . env('DOCKERIZE_IMAGE') . ':' . env('DOCKERIZE_VERSION') . '-' . env('DOCKERIZE_BRANCH');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (!$this->option('run'))
        {
            $this->error('ERROR: This artisan command must only run from Dockerfile.');

            return -1;
        }

        $this->runBuildCommands();

        return 0;
    }

    /**
     * Run all configured build commands during "docker build ..." process.
     */
    public function runBuildService(): void
    {
        $this->info('Execute build service for ' . $this->name . ' ...');

        /** @var DockerBuildImageService $service */
        $service = app()->make(DockerBuildImageService::class);

        $service->run();

        $this->info('Finished.');
    }

    /**
     * Run all configured build commands during "docker build ..." process.
     */
    public function runBuildCommands(): void
    {
        $this->info('Execute build commands for ' . $this->name . ' ...');

        $artisan = json_decode(env('DOCKERIZE_BUILD_COMMANDS'), true) ?? [];

        foreach ($artisan as $command)
        {
            $this->info("Running $command.");

            if (Artisan::call($command))
            {
                $this->error(trim(Artisan::output()));
            }
        }

        $this->info('Finished.');
    }
}

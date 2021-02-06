<?php

namespace janole\Laravel\Dockerize\Console\Commands;

use Illuminate\Console\Command;
use Dotenv\Dotenv;

class DockerBuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:build'
        . ' {--p|print : Only print the Dockerfile}'
        . ' {--s|save : Only save the Dockerfile} '
        . ' {--P|push : Push the image}'
        . ' {--I|print-image-tag : Only print the image tag}';

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
        $this->build(false);
    }

    /**
     * ...
     *
     * @return mixed
     */
    public function build($silent = false)
    {
        static::loadConfig();

        $buildpath = ".";

        /* ignore for now ... TODO: make it optional later on ...
        // Copy our own .dockerignore (potentially dangerous!)
        $dockerignore = file_get_contents(base_path("vendor/janole/laravel-dockerize/docker/dockerignore"));
        file_put_contents(base_path(".dockerignore"), $dockerignore);
        */

        //
        if (@strlen(env("DOCKERIZE_IMAGE")) == 0)
        {
            $this->error("DOCKERIZE_IMAGE missing. Please specify a base name for your docker image.");

            exit(-1);
        }

        //
        $dockerfile = file_get_contents(base_path("vendor/janole/laravel-dockerize/docker/Dockerfile"));

        //
        $dockerfile = str_replace('${DOCKERIZE_BASE_IMAGE}', env("DOCKERIZE_BASE_IMAGE", "janole/laravel-nginx-postgres"), $dockerfile);

        //
        if (($env = env("DOCKERIZE_ENV")) && file_exists(base_path($env)))
        {
            $dockerfile = str_replace('${DOCKERIZE_ENV}', $env, $dockerfile);
        }
        else if (($env = ".env") && file_exists(base_path($env)))
        {
            $dockerfile = str_replace('${DOCKERIZE_ENV}', $env, $dockerfile);
        }
        else
        {
            $this->error("Cannot find a proper .env file!");

            exit(-2);
        }

        //
        $dockerfile = str_replace('${DOCKERIZE_LOCALE}', env("DOCKERIZE_LOCALE", config("app.locale")), $dockerfile);

        //
        $imageInfo = static::getImageInfo();

        //
        if ($this->option("print-image-tag"))
        {
            $this->info($imageInfo["image"]);

            return 0;
        }

        $dockerfile = str_replace('${DOCKERIZE_VERSION}', $imageInfo["version"], $dockerfile);
        $dockerfile = str_replace('${DOCKERIZE_BRANCH}', $imageInfo["branch"], $dockerfile);
        $dockerfile = str_replace('${DOCKERIZE_COMMIT}', $imageInfo["commit"], $dockerfile);

        //
        $dockerfile = str_replace('${DOCKERIZE_CONTAINER_USER}', env("DOCKERIZE_CONTAINER_USER", "root"), $dockerfile);

        //
        if ($this->option("print"))
        {
            $this->info($dockerfile);

            return 0;
        }

        //
        $dockerfile = "# Dynamic Dockerfile\n# !!! DO NOT EDIT THIS FILE BY HAND -- YOUR CHANGES WILL BE OVERWRITTEN !!!\n\n$dockerfile";
        @mkdir(base_path($buildpath));
        file_put_contents(base_path("$buildpath/Dockerfile"), $dockerfile);
        
        //
        if ($this->option("save"))
        {
            $this->info("Dockerfile saved to " . base_path("$buildpath/Dockerfile"));

            return 0;
        }

        //
        $cmd = "cd " . base_path() . " && docker build -t " . $imageInfo["image"] . " -f $buildpath/Dockerfile .";

        //
        if ($this->option("push"))
        {
            $cmd .= " && docker push " . $imageInfo["image"];
        }

        //
        $this->info($cmd);

        $fd = popen("($cmd) 2>&1", "r");

        while (($line = fgets($fd)) !== false)
        {
            $this->line("* " . trim($line));
        }

        pclose($fd);

        return 0;
    }

    public static function loadConfig()
    {
        static::loadEnv(".dockerize.env");

        if (($cfg = env("COMPOSE_PROJECT_NAME")))
        {
            static::loadEnv(".$cfg/dockerize.env");
        }

        if (($cfg = env("APP_ENV")))
        {
            static::loadEnv(".$cfg/dockerize.env");
        }
    }

    private static function loadEnv($file)
    {
        if (file_exists(base_path($file)))
        {
            try
            {
                (new Dotenv(base_path(), $file))->overload();
            }
            catch (\Throwable $th)
            {
                Dotenv::create(base_path(), $file)->overload();
            }
        }
    }

    public static function getImageInfo()
    {
        if (@strlen(($IMAGE = env("DOCKERIZE_IMAGE"))) == 0)
        {
            return null;
        }

        //
        if (($VERSION = env("DOCKERIZE_VERSION", ":git")) == ":git")
        {
            $VERSION = env("APP_VERSION");

            if (@strlen($BUILD = static::gitCountRefs()) > 0)
            {
                $VERSION .= (@strlen($VERSION) > 0 ? "." : "") . $BUILD;
            }
        }

        //
        if (@strlen($VERSION) == 0)
        {
            $VERSION = env("APP_VERSION", "0.0");
        }

        //
        if (($BRANCH = env("DOCKERIZE_BRANCH", ":git")) == ":git")
        {
            $BRANCH = static::gitCurrentBranch();
            $BRANCH = preg_replace("/[^0-9a-z.]/i", "-", $BRANCH);
        }

        //
        if (strlen($VERSION) > 0)
        {
            $IMAGE.= (strpos($IMAGE, ":") !== false ? "-" : ":") . $VERSION;
        }

        if (strlen($BRANCH) > 0)
        {
            $IMAGE.= (strpos($IMAGE, ":") !== false ? "-" : ":") . $BRANCH;
        }

        return ["image" => $IMAGE, "version" => $VERSION, "branch" => $BRANCH, "commit" => static::gitCurrentCommit()];
    }

    private static function gitCurrentBranch()
    {
        $cmd = env("DOCKERIZE_GIT", "git") . " rev-parse --abbrev-ref HEAD 2>/dev/null";

        return @exec($cmd);
    }

    private static function gitCurrentCommit()
    {
        $cmd = env("DOCKERIZE_GIT", "git") . " rev-parse --short HEAD 2>/dev/null";

        return @exec($cmd);
    }

    private static function gitCountRefs()
    {
        $cmd = env("DOCKERIZE_GIT", "git") . " rev-list HEAD --count 2>/dev/null";

        return @exec($cmd);
    }
}

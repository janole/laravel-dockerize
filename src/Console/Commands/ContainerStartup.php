<?php

namespace janole\Laravel\Dockerize\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ContainerStartup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'container:startup {--F|force-first : Force running all first class seeders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all necessary steps like migrations, seeding, ...';

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
        return $this->startup();
    }

    /**
     * Run all necessary steps to init the Laravel app.
     *
     * @return mixed
     */
    public function startup()
    {
        $this->info("Init " . env("APP_NAME") . "/" . env("DOCKERIZE_IMAGE") . ":" . env("DOCKERIZE_VERSION") . "-" . env("DOCKERIZE_BRANCH") . " ...");

        if ($this->waitForDatabase() != 0)
        {
            $db = config("database.connections." . config("database.default"));

            $this->error("ERROR: Cannot connect to database " . @$db["host"] . ":" . @$db["port"] . " / " . @$db["database"]);

            return -1;
        }

        if (!($time = $this->lastUpdated()) && !$this->option("force-first"))
        {
            return 0;
        }

        $firstRun = !Schema::hasTable('users');

        if (!$firstRun && $this->option("force-first") && $this->confirm("** WARNING! Use first-run/init seeders? You might lose data!"))
        {
            $firstRun = true;
        }

        $this->info(($firstRun ? "First run" : "Run migrations") . " (" . $time . ") ...");

        Artisan::call("migrate", ["--force" => true]);
        $this->info(trim(Artisan::output()));

        $id = $firstRun ? "1" : "2";

        $classes = json_decode(env("DOCKERIZE_SEED$id"), true) ?? [];
        $artisan = json_decode(env("DOCKERIZE_ARTISAN$id"), true) ?? [];

        foreach ($classes as $class)
        {
            $this->info("Running $class.");

            if (Artisan::call("db:seed", ["--force" => true, "--class" => "$class"]))
            {
                $this->error(trim(Artisan::output()));
            }
        }

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

    public function waitForDatabase()
    {
        for ($i = 0; $i < 60; $i++)
        {
            try
            {
                if (DB::connection()->getPdo())
                {
                    return 0;
                }
            }
            catch (\Exception $e)
            {
                $msg = "** Waiting for database ...";

                if (config("app.debug"))
                {
                    $msg .= " (" . $e->getMessage() . ")";
                }

                $this->info($msg);
            }

            sleep(1);
        }

        return -1;
    }

    public function lastUpdated()
    {
        if (!Schema::hasTable('rfInternal'))
        {
            DB::transaction(function()
            {
                Schema::create('rfInternal', function (Blueprint $table)
                {
                    $table->increments('id');
                    $table->string('name');
                    $table->longText('value')->nullable();
                    $table->timestamps();
                });
            });
        }

        $this->info("Checking Database ...");

        $time = microtime(true);

        if (!($lock = DB::table('rfInternal')->where('name', 'lock.db')->lockForUpdate()->first()))
        {
            DB::table('rfInternal')->insert(["name" => "lock.db", "value" => $time]);
        }
        else if (@floatval($lock->value) > $time - 10.0)
        {
            $this->info("Init skipped.");

            return false;
        }

        DB::table('rfInternal')->where("name", "lock.db")->lockForUpdate()->update(["value" => $time]);

        // check ...

        return (DB::table('rfInternal')->where('name', 'lock.db')->lockForUpdate()->first()->value == "$time") ? "$time" : false;
    }
}

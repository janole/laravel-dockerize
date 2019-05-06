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
    protected $signature = 'container:startup';

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
        return static::startup(false);
    }

    /**
     * Run all necessary steps to init the Laravel app.
     *
     * @return mixed
     */
    public static function startup($silent = false)
    {
        if (!$silent)
        {
            echo "Init " . env("APP_NAME", "Laravel") . "/" . env("APP_VERSION", "0.0.0") . " ...\n";
        }

        if (static::waitForDatabase() != 0)
        {
            echo "ERROR: Cannot connect to Database " . env("DB_HOST") . " / " . env("DB_DATABASE") . "\n";

            return -1;
        }

        if (!($time = static::lastUpdated()))
        {
            echo $time;
            return 0;
        }

        $firstRun = (!Schema::hasTable('users'));

        echo ($firstRun ? "First run" : "Run migrations") . " (" . $time . ") ...\n";

        Artisan::call("migrate", ["--force" => true]);
        echo Artisan::output();

        $UserSeeder = env("USER_SEEDER", "DemoUserSeeder");
        $PasswordSeeder = env("PASSWORD_SEEDER", "PasswordSeeder");

        if ($firstRun)
        {
            $classes = ["CatalogSeeder", "CountrySeeder", $UserSeeder, "ExternalStateSeeder", "GroupSeeder", "MenuSeeder", "SimpleStateSeeder", "StateSeeder", "UserStateSeeder", "TemplateSeeder", $PasswordSeeder];

            if (($seeder = env("OPTIONS_ROCKETFORM_STARTUP_INIT_SEEDER")))
            {
                $classes = json_decode($seeder, true);
            }
        }
        else
        {
            $classes = ["MenuSeeder", "StateSeeder", "UserStateSeeder", $PasswordSeeder];

            if (($seeder = env("OPTIONS_ROCKETFORM_STARTUP_UPDATE_SEEDER")))
            {
                $classes = json_decode($seeder, true);
            }
        }

        foreach ($classes as $class)
        {
            echo "Running $class.\n";

            Artisan::call("db:seed", ["--force" => true, "--class" => "$class"]);
            echo Artisan::output();
        }

        echo "Finished.\n";

        return 0;
    }

    public static function waitForDatabase()
    {
        for ($i = 0; $i < 60; $i++)
        {
            usleep(1000 * ($i == 0 ? random_int(100, 1000) : 1000));

            try
            {
                if (DB::connection()->getPdo())
                {
                    return 0;
                }
            }
            catch (\Exception $e)
            {
                echo "Waiting for database ...";

                if (env("APP_DEBUG"))
                {
                    echo " (" . $e->getMessage() . ")";
                }

                var_dump($e);

                echo "\n";
            }
        }

        return -1;
    }

    public static function lastUpdated()
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

        echo "Checking Database ...\n";

        $time = microtime(true);

        if (!($lock = DB::table('rfInternal')->where('name', 'lock.db')->lockForUpdate()->first()))
        {
            DB::table('rfInternal')->insert(["name" => "lock.db", "value" => $time]);
        }
        else
        if (@floatval($lock->value) > $time - 10.0)
        {
            echo "Init skipped.\n";

            return false;
        }

        DB::table('rfInternal')->where("name", "lock.db")->lockForUpdate()->update(["value" => $time]);

        // check ...

        return (DB::table('rfInternal')->where('name', 'lock.db')->lockForUpdate()->first()->value == "$time") ? "$time" : false;
    }
}

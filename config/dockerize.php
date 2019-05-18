<?php

return
[
	"env" => env("DOCKERIZE_ENV", env("APP_ENV", ".env")),

    "image" => env("DOCKERIZE_IMAGE"),
    "base-image" => env("DOCKERIZE_BASE_IMAGE", "janole/laravel-nginx-postgres:unoconv"),

    "share" => env("DOCKERIZE_SHARE"),

    "seed1" => env("DOCKERIZE_SEED1", '["DatabaseSeeder"]'),
    "seed2" => env("DOCKERIZE_SEED2"),

    "host" => env("DOCKERIZE_HOST", "localhost"),
    "port" => env("DOCKERIZE_PORT", "3333"),

    "version" => env("DOCKERIZE_VERSION", ":git"),

    "branch" => env("DOCKERIZE_BRANCH", ":git"),
];

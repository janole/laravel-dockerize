# laravel-dockerize
A very simple tool to containerize your Laravel App ...

## What? Why?
Do you want to easily create a Docker image from your Laravel App? Then this project wants to become your friend ;-)

> !! NOTICE !! So far, this project is severely limited to some base dependencies (like PostgreSQL for example.)

## Requirements

- PHP 8.2 or newer
- Laravel 11, 12 or 13
- Docker with `buildx`

Laravel 10 and older, and PHP 7.4–8.1, are supported up to version 1.x.

## Installation
```console
$ composer require janole/laravel-dockerize
```

## Usage

**1. First, you need to specify the (base-)name of the docker image**. Add the following variable to your `.env` file:

```
DOCKERIZE_IMAGE="my-shiny-new-app"
```

or

```
DOCKERIZE_IMAGE="my-own.docker-registry.com/my-shiny-new-app"
```

> If you're using git, `laravel-dockerize` will try to automatically add some version and branch tags to the image name.

**2. Now build the image:**

```console
$ php artisan docker:build
cd /path/to/test-app && docker buildx build -t my-shiny-new-app:0.1-master --pull -f ./Dockerfile .
...
* Successfully tagged my-shiny-new-app:0.1-master
```

Yay! Now you've got your first image named `my-shiny-new-app:0.1-master`

> !! NOTICE !! The Dockerfile used to create the image will be automatically saved in your project's root.

**3. Create a docker-compose.yml file in the project root:**

```console
$ php artisan docker:compose -s 
File saved as /path/to/test-app/docker-compose.yml
```

**4. Run the project via docker-compose:**

```console
$ docker-compose up [-d]
Creating network "test-app_default" with the default driver
Creating volume "test-app_postgres-data" with default driver
Creating test-app_database_1 ... done
Creating test-app_app_1      ... done
```

### Internals ...

- **laravel-dockerize** will add another *artisan* command to your project: `container:startup`. This command will be automatically called each time the container is (re-)started and it will try to initialize the database for you. For this, it will wait for the database to be ready, call `php artisan migrate --force` and then try to run the initial seeders `DOCKERIZE_SEED1` or updating seeders `DOCKERIZE_SEED2` (if the database wasn't fresh.)

## Configuration

All settings are read from `.env`, `.dockerize.env`, `.<COMPOSE_PROJECT_NAME>/dockerize.env` and `.<APP_ENV>/dockerize.env` (later files override earlier ones).

| Setting | Default | Meaning |
|---|---|---|
| `DOCKERIZE_IMAGE` | (required) | Image name, e.g. `my-registry.com/my-app`. Version and branch are appended as the tag. |
| `DOCKERIZE_BASE_IMAGE` | `janole/laravel-apache-postgres:0-php85` | Image to build `FROM`. See [laravel-apache-postgres](https://github.com/janole/laravel-apache-postgres) for the PHP 8.3, 8.4 and 8.5 variants. |
| `DOCKERIZE_VERSION` | `APP_VERSION` + git commit count | Version part of the image tag. |
| `DOCKERIZE_BRANCH` | current git branch | Branch part of the image tag. |
| `DOCKERIZE_ENV` | `.env` | The env file copied into the image as `/app/.env`. |
| `DOCKERIZE_LOCALE` | `app.locale` | Locale generated in the image. |
| `DOCKERIZE_CONTAINER_USER` | `www-data` | User that owns `/app` and runs composer and the build scripts. |
| `DOCKERIZE_PLATFORM` | (native) | `--platform` for `docker buildx build`, e.g. `linux/amd64`. |
| `DOCKERIZE_BUILD_COMMANDS` | | JSON list of artisan commands to run during the image build. |
| `DOCKERIZE_SEED1` / `DOCKERIZE_SEED2` | | JSON list of seeder classes to run on the first start (no `users` table yet) / on later starts. |
| `DOCKERIZE_ARTISAN1` / `DOCKERIZE_ARTISAN2` | | JSON list of artisan commands to run on the first start (no `users` table yet) / on later starts. |
| `DOCKERIZE_PORT` / `DOCKERIZE_HOST` | `3333` / `localhost` | Port and host for `docker-compose.yml` and `APP_URL`. |
| `DOCKERIZE_CONTAINER_PORT` | `8080` | Port the app listens on inside the container. |
| `DOCKERIZE_SHARE` | | JSON list of volumes for the app service. |
| `DOCKERIZE_POSTGRES_IMAGE` | `postgres:16` | Database image in `docker-compose.yml`. |
| `DOCKERIZE_COMPOSE_WITH_SCHEDULER` / `_QUEUE` | | Add scheduler / queue worker services to `docker-compose.yml`. |
| `DOCKERIZE_COMPOSE_ENV_<NAME>` | | Sets `<NAME>` in the app's environment in `docker-compose.yml`. |
| `DOCKERIZE_COMPOSE_TRANSFORMER` | | PHP file returning `function (array $yaml): array` to adjust `docker-compose.yml`. |

## Upgrading from 1.x to 2.0

- **Requirements:** PHP 8.2+ and Laravel 11–13 (see above).
- **New default base image:** `janole/laravel-apache-postgres:0-php85` instead of `0-php83`. To stay on PHP 8.3 (e.g. because a dependency doesn't support 8.5 yet), set `DOCKERIZE_BASE_IMAGE="janole/laravel-apache-postgres:0-php83"`.
- **Composer runs as the container user:** `composer install`, `view:clear` and the image build scripts now run as `DOCKERIZE_CONTAINER_USER` instead of root, so every file in `/app` belongs to that user. A build service (`DockerBuildImageService`) that needs root can't do that any more.
- **`DOCKERIZE_SPLIT_VENDOR` was removed,** together with its alternative Dockerfile. The setting is ignored.
- **Config errors are no longer ignored:** a `.dockerize.env` that can't be parsed now stops `docker:build` and `docker:compose` with an error.

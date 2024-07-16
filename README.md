# laravel-dockerize
A very simple tool to containerize your Laravel App ...

## What? Why?
Do you want to easily create a Docker image from your Laravel App? Then this project wants to become your friend ;-)

> !! NOTICE !! So far, this project is severely limited to some base dependencies (like PostgreSQL for example.)

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
cd /Users/ole/projects/Laravel/test-app && docker build -t my-shiny-new-app:0.1-master .
...
* Successfully tagged my-shiny-new-app:0.1-master
```

Yay! Now you've got your first image named `my-shiny-new-app:0.1-master`

> !! NOTICE !! The Dockerfile used to create the image will be automatically saved in your project's root.

**3. Create a docker-compose.yml file in the project root:**

```console
$ php artisan docker:compose -s 
File saved as /Users/ole/projects/Laravel/test-app/docker-compose.yml
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

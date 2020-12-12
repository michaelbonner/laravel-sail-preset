<?php

namespace Yoelpc4\LaravelSailPreset;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class SailPresetServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            Artisan::command('sail:publish-preset', function () {
                if ($this->app->bound(\Laravel\Sail\SailServiceProvider::class)) {
                    if ($this->confirm('Would you like to replace current runtimes & docker-compose.yml from the preset?', false)) {
                        $this->call('vendor:publish', ['--tag' => 'sail']);

                        $this->call('vendor:publish', ['--tag' => 'sail-preset']);

                        file_put_contents(
                            base_path('docker-compose.yml'),
                            str_replace(
                                './vendor/laravel/sail/runtimes/8.0',
                                './docker/8.0',
                                file_get_contents(base_path('docker-compose.yml'))
                            )
                        );

                        $this->info('Successfully publishing laravel sail preset, please run "sail build --no-cache" to re-building docker image.');
                    }
                } else {
                    $this->error('The laravel/sail package is not installed, please run "composer require laravel/sail --dev && php artisan sail:install" in advance.');
                }
            })->purpose('Publish the Laravel Sail Docker preset files.');

            $this->publishes([
                __DIR__.'/../stubs/docker-compose.yml' => base_path('docker-compose.yml'),
                __DIR__.'/../runtimes/7.4/Dockerfile'  => base_path('docker/7.4/Dockerfile'),
                __DIR__.'/../runtimes/7.4/php.ini'     => base_path('docker/7.4/php.ini'),
                __DIR__.'/../runtimes/8.0/Dockerfile'  => base_path('docker/8.0/Dockerfile'),
                __DIR__.'/../runtimes/8.0/php.ini'     => base_path('docker/8.0/php.ini'),
            ], 'sail-preset');
        }
    }

    /**
     * @inheritDoc
     */
    public function provides()
    {
        return [
            'sail.publish-preset-command',
        ];
    }
}

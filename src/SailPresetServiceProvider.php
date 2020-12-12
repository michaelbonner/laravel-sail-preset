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
                if ($this->confirm('Do you want to replace the current runtimes & docker-compose.yml with preset?',
                    false)) {
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

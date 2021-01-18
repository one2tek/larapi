<?php

namespace one2tek\larapi\Providers;

use one2tek\larapi\Routes\ApiConsumerRouter;
use Illuminate\Support\ServiceProvider;
use one2tek\larapi\Console\ComponentMakeCommand;

class LarapiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__. '../../Config/larapi.php',
            'larapi'
        );
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__. '/../Config/larapi.php' => config_path('larapi.php'),
        ]);
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                ComponentMakeCommand::class
            ]);
        }

        $this->app->singleton('apiconsumer', function () {
            $app = app();

            return new ApiConsumerRouter($app, $app['request'], $app['router']);
        });
    }
}

<?php

namespace one2tek\larapi\Providers;

use one2tek\larapi\Routes\Router;
use Illuminate\Support\ServiceProvider as BaseProvider;
use one2tek\larapi\Console\ComponentMakeCommand;

class LaravelServiceProvider extends BaseProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__. '../../Config/larapi-components.php', 'larapi-components'
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
            __DIR__. '/../Config/larapi-components.php' => config_path('larapi-components.php'),
        ]);
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                ComponentMakeCommand::class
            ]);
        }

        $this->app->singleton('apiconsumer', function () {
            $app = app();

            return new Router($app, $app['request'], $app['router']);
        });
    }
}

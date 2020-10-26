<?php

namespace one2tek\larapi\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot()
    {
        Route::pattern('id', '[0-9]+');
        
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $middleware = config('larapi-components.protection_middleware');
        $extraRoutes = config('larapi-components.extra_routes');

        $highLevelParts = array_map(function ($namespace) {
            return glob(sprintf('%s%s*', $namespace, DIRECTORY_SEPARATOR), GLOB_ONLYDIR);
        }, config('larapi-components.namespaces'));

        foreach ($highLevelParts as $part => $partComponents) {
            foreach ($partComponents as $componentRoot) {
                $component = substr($componentRoot, strrpos($componentRoot, DIRECTORY_SEPARATOR) + 1);

                $namespace = sprintf(
                    '%s\\%s\\Controllers',
                    $part,
                    $component
                );

                $fileNames = [
                    'routes' => true,
                    'routes_protected' => true,
                    'routes_public' => false,
                ];

                foreach ($fileNames as $fileName => $protected) {
                    $path = sprintf('%s/%s.php', $componentRoot, $fileName);

                    if (!file_exists($path)) {
                        continue;
                    }

                    $router->group([
                        'middleware' => $protected ? $middleware : [],
                        'namespace'  => $namespace,
                    ], function ($router) use ($path) {
                        require $path;
                    });
                }

                foreach ($extraRoutes as $routeName => $route) {
                    $path = sprintf('%s/%s.php', $componentRoot, $routeName);

                    if (!file_exists($path)) {
                        continue;
                    }
                    
                    $namespace = sprintf(
                        '%s\\%s\\'. $route['namespace'],
                        $part,
                        $component
                    );

                    $router->group([
                        'middleware' => $route['middleware'],
                        'namespace' => $namespace,
                        'prefix' => $route['prefix']
                    ], function ($router) use ($path) {
                        require $path;
                    });
                }
            }
        }
    }
}

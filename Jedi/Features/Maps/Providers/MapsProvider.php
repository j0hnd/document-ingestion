<?php
namespace Jedi\Features\Maps\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\App;

use Jedi\Repositories\ValidatorRepository;
use Jedi\Features\Maps\Repositories\MapsRepository;
use Jedi\Features\Maps\Models\MapsModel;

class MapsProvider extends ServiceProvider
{
    public function boot()
    {
        include_once  __DIR__.'/../routes.php';
        $this->loadViewsFrom(__DIR__ . '/../Views', 'Maps');
    }

    public function register()
    {
        $this->app->bind('Jedi\Features\Maps\Repositories\MapsInterface', function ($app) {
            return new MapsRepository(new MapsModel, new ValidatorRepository);
        });

       
    }

    protected function loadViewsFrom($path, $namespace)
    {
        if (is_dir($appPath = $this->app->basePath().'/resources/views/vendor/'.$namespace))
        {
            $this->app['view']->addNamespace($namespace, $appPath);
        }

        $this->app['view']->addNamespace($namespace, $path);
    }
}
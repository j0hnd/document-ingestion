<?php
namespace Jedi\Features\Test\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\App;

use Jedi\Repositories\ValidatorRepository;
use Jedi\Features\Test\Repositories\TestRepository;
use Jedi\Features\Test\Repositories\RegisterRepository;
use Jedi\Features\Test\Models\TestModel;
use Jedi\Features\Test\Models\RegisterModel;

class TestProvider extends ServiceProvider
{
    public function boot()
    {
        include_once  __DIR__.'/../routes.php';
        $this->loadViewsFrom(__DIR__ . '/../Views', 'Test');
    }

    public function register()
    {
        $this->app->bind('Jedi\Features\Test\Repositories\TestInterface', function ($app) {
            return new TestRepository(new TestModel, new ValidatorRepository);
        });

        $this->app->bind('Jedi\Features\Test\Repositories\RegisterInterface', function ($app) {
            return new RegisterRepository(new RegisterModel, new ValidatorRepository);
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
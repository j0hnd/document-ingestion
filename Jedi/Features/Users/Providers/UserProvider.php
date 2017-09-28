<?php
namespace Jedi\Features\Users\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\App;

use Jedi\Features\Users\Repositories\UserRepository;
use Jedi\Features\Users\Repositories\AuthenticateRepository;
use Jedi\Models\UsersModel;
use Jedi\Repositories\ValidatorRepository;


class UserProvider extends ServiceProvider
{

    public function boot()
    {
        include_once  __DIR__.'/../routes.php';
        $this->loadViewsFrom(__DIR__ . '/../Views', 'users');
    }

    public function register()
    {
        $this->app->bind('Jedi\Features\Users\Repositories\UserInterface', function ($app) {
            return new UserRepository(new UsersModel , new ValidatorRepository);
        });

        $this->app->bind('Jedi\Features\Users\Repositories\AuthenticateInterface', function ($app) {
            return new AuthenticateRepository(new UsersModel , new ValidatorRepository);
        });
    }

    protected function loadViewsFrom($path, $namespace)
    {
        if (is_dir($appPath = $this->app->basePath().'/resources/views/vendor/'.$namespace)) {
            $this->app['view']->addNamespace($namespace, $appPath);
        }

        $this->app['view']->addNamespace($namespace, $path);
    }
}
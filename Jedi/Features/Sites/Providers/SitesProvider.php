<?php
namespace Jedi\Features\Sites\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\App;

use Jedi\Features\Sites\Repositories\SitesRepository;
use Jedi\Features\Sites\Repositories\InputTemplatesRepository;

use Jedi\Features\Sites\Models\SitesModel;
use Jedi\Features\Sites\Models\InputTemplatesModel;

use Jedi\Repositories\ValidatorRepository;


class SitesProvider extends ServiceProvider
{

    public function boot()
    {
        include_once  __DIR__.'/../routes.php';
        $this->loadViewsFrom(__DIR__ . '/../Views', 'sites');
    }

    public function register()
    {
        $this->app->bind('Jedi\Features\Sites\Repositories\SitesInterface', function ($app) {
            return new SitesRepository(new SitesModel , new ValidatorRepository);
        });

        $this->app->bind('Jedi\Features\Sites\Repositories\InputTemplatesInterface', function ($app) {
            return new InputTemplatesRepository(new InputTemplatesModel , new ValidatorRepository);
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
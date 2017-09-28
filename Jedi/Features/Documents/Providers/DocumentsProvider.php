<?php
namespace Jedi\Features\Documents\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\App;

use Jedi\Features\Documents\Repositories\UploadsRepository;
use Jedi\Features\Documents\Repositories\DocumentsRepository;
use Jedi\Features\Documents\Repositories\DocumentMetasRepository;
use Jedi\Features\Documents\Repositories\DocumentDetailsRepository;
use Jedi\Features\Documents\Repositories\BatchRepository;

use Jedi\Features\Documents\Models\DocumentsModel;
use Jedi\Features\Documents\Models\DocumentMetasModel;
use Jedi\Features\Documents\Models\DocumentDetailsModel;
use Jedi\Features\Documents\Models\BatchModel;

use Jedi\Repositories\ValidatorRepository;


class DocumentsProvider extends ServiceProvider
{
    public function boot()
    {
        include_once  __DIR__.'/../routes.php';
        $this->loadViewsFrom(__DIR__ . '/../Views', 'Documents');
    }

    public function register()
    {
        $this->app->bind('Jedi\Features\Documents\Repositories\UploadsInterface', function ($app) {
            return new UploadsRepository(new DocumentsModel, new ValidatorRepository);
        });
        
        $this->app->bind('Jedi\Features\Documents\Repositories\DocumentsInterface', function ($app) {
            return new DocumentsRepository(new DocumentsModel, new ValidatorRepository);
        });

        $this->app->bind('Jedi\Features\Documents\Repositories\BatchInterface', function ($app) {
            return new BatchRepository(new BatchModel, new ValidatorRepository);
        });

        $this->app->bind('Jedi\Features\Documents\Repositories\DocumentMetasInterface', function ($app) {
            return new DocumentMetasRepository(new DocumentMetasModel, new ValidatorRepository);
        });

        $this->app->bind('Jedi\Features\Documents\Repositories\DocumentDetailsInterface', function ($app) {
            return new DocumentDetailsRepository(new DocumentDetailsModel, new ValidatorRepository);
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
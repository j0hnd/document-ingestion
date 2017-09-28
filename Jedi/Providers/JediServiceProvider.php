<?php
namespace Jedi\Providers;

use Illuminate\Support\ServiceProvider;
use App;

class JediServiceProvider extends ServiceProvider
{
    public function boot()
    {
        include_once  __DIR__.'/../filters.php';
        include_once  __DIR__.'/../functions.php';
        include_once  __DIR__.'/../config.php';
        include_once  __DIR__.'/../routes.php';

    }

    public function register()
    {
        $this->app->bind('Jedi\Repositories\ValidatorInterface', 'Jedi\Repositories\ValidatorRepository');
        $this->app->bind('Jedi\Repositories\SystemLogsInterface', 'Jedi\Repositories\SystemLogsRepository');

        $this->app->register('Jedi\Features\Documents\Providers\DocumentsProvider');
        $this->app->register('Jedi\Features\Users\Providers\UserProvider');
        $this->app->register('Jedi\Features\Sites\Providers\SitesProvider');
        $this->app->register('Jedi\Features\Test\Providers\TestProvider');
        $this->app->register('Jedi\Features\Maps\Providers\MapsProvider');
        
    }
}
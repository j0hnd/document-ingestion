<?php
namespace Jedi\Features\Sites\Models;

use Jedi\Models\AbstractModel;

class SitesModel extends AbstractModel
{
    protected $table  = 'sites';

    protected $guards = ['id'];

    protected $fillable = ['site_name', 'description'];
}
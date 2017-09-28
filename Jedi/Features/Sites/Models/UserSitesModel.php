<?php
namespace Jedi\Features\Sites\Models;

use Jedi\Models\AbstractModel;

class UserSitesModel extends AbstractModel
{
    protected $table  = 'user_sites';

    protected $guards = ['id'];

    protected $fillable = ['user_id', 'site_id', 'is_active'];
}
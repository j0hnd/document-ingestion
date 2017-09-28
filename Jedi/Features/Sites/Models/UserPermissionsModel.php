<?php
namespace Jedi\Features\Sites\Models;

use Jedi\Models\AbstractModel;

class UserPermissionsModel extends AbstractModel
{
    protected $table  = 'user_permissions';

    protected $guards = ['id'];

    protected $fillable = ['user_site_id', 'permission_id'];
}
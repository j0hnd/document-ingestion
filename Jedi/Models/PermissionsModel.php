<?php
namespace Jedi\Models;

class PermissionsModel extends AbstractModel {

    protected $table  = 'permissions';

    protected $guards = ['id'];

}
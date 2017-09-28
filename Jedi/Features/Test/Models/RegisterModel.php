<?php
namespace Jedi\Features\Test\Models;

use Jedi\Models\AbstractModel;

class RegisterModel extends AbstractModel
{
    protected $table  = 'user_details';

    protected $guards = ['id'];

    protected $fillable = [];
}
<?php
namespace Jedi\Features\Test\Models;

use Jedi\Models\AbstractModel;

class TestModel extends AbstractModel
{
    protected $table  = 'test';

    protected $guards = ['id'];

    protected $fillable = [];
}
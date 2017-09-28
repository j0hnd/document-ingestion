<?php
namespace Jedi\Features\Maps\Models;

use Jedi\Models\AbstractModel;

class FieldnamesModel extends AbstractModel
{
    protected $table  = 'fieldnames';

    protected $guards = ['id'];

    protected $fillable = [];
}
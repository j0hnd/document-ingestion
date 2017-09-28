<?php
namespace Jedi\Features\Maps\Models;

use Jedi\Models\AbstractModel;

class MapsModel extends AbstractModel
{
    protected $table  = 'mappings';

    protected $guards = ['id'];

    protected $fillable = [];
}
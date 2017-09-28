<?php
namespace Jedi\Features\Sites\Models;

use Jedi\Models\AbstractModel;

class InputTemplatesModel extends AbstractModel
{
    protected $table  = 'input_templates';

    protected $guards = ['id'];

    protected $fillable = ['site_id', 'filename', 'location', 'is_active'];
}
<?php
namespace Jedi\Features\Documents\Models;

use Jedi\Models\AbstractModel;


class MetaDetailsModel extends AbstractModel
{
    protected $table  = 'meta_details';

    protected $guards = ['id'];
}

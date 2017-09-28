<?php
namespace Jedi\Features\Documents\Models;

use Jedi\Models\AbstractModel;


class DocumentsModel extends AbstractModel
{
    protected $table  = 'documents';

    protected $guards = ['id'];
}

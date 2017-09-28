<?php
namespace Jedi\Features\Documents\Models;

use Jedi\Models\AbstractModel;


class DocumentMetasModel extends AbstractModel
{
    protected $table  = 'document_metas';

    protected $guards = ['id'];

    protected $fillable = ['document_id', 'meta_group', 'extras'];
}

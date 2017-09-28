<?php namespace Jedi\Features\Documents\Models;

use Jedi\Models\AbstractModel;


class DocumentDetailsModel extends AbstractModel
{
    protected $table  = 'document_details';

    protected $guards = ['id'];

    protected $fillable = ['document_meta_id', 'document_name', 'status', 'notes', 'checked_by'];
}
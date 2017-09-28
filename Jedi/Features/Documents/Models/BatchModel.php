<?php
namespace Jedi\Features\Documents\Models;

use Jedi\Models\AbstractModel;

class BatchModel extends AbstractModel
{
    protected $table  = 'batch';

    protected $guards = ['id'];

    protected $fillable = ['upload_name', 'site_id', 'source', 'output_destination_id', 'status'];
}
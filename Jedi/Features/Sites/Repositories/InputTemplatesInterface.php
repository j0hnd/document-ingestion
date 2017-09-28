<?php
namespace Jedi\Features\Sites\Repositories;

use Illuminate\Database\Eloquent\Model;

interface InputTemplatesInterface
{
    public function read_input_template($site_id);
}
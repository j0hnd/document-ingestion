<?php namespace Jedi\Features\Documents\Repositories;

use Illuminate\Database\Eloquent\Model;

interface DocumentDetailsInterface
{
    public function update_document($document_id, $data);
    public function validate_inputs (array $inputs);
}
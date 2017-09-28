<?php namespace Jedi\Features\Documents\Repositories;

use Illuminate\Database\Eloquent\Model;

interface DocumentMetasInterface
{
    public function save_meta(array $attributes);
    public function get_document_files($document_meta_id);
    public function get_document_meta_details($document_meta_id);
    public function validate_inputs (array $inputs);
}
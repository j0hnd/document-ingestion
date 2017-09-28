<?php
namespace Jedi\Features\Documents\Repositories;

use Illuminate\Database\Eloquent\Model;

interface DocumentsInterface
{
    public function get_batch($filters = null);
    public function save(array $inputs);
    public function get_output_destinations();
    public function convert_pdf_to_image($file, $filename);
    public function reference_document_to_batch($document_id, $batch_id, $action_by);
    public function extract($file, $data);
    public function map($data_source, $attributes);
    public function get_required_fields($input_file_name, $attributes);
    public function get_document_details($document_id, $document_meta_id);
    public function check_if_file_exists($attributes);
    public function map_pdf($data, $xml, $document_id);
    public function extract_it($data);
    public function update_document_extras($document_id, $extra);
}

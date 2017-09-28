<?php namespace Jedi\Features\Documents\Repositories;

use Illuminate\Database\Eloquent\Model;

interface BatchInterface
{
    public function update_batch_status($batch_id, $updated_by, array $documents);
    public function validate_inputs (array $inputs);
    public function get_batch($status = BATCH_STATUS_PENDING);
}
<?php
namespace Jedi\Features\Sites\Repositories;

use Illuminate\Database\Eloquent\Model;

interface SitesInterface
{
    public function get_all_active_sites();
    public function get_site_details($site_id);
    public function save_templates(array $data);
    public function set_template_data($site_id, array $data);
    public function update_template($site_id, array $attributes);
    public function validate_inputs(array $inputs);
}
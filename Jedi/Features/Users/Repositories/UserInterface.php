<?php
namespace Jedi\Features\Users\Repositories;

use Illuminate\Database\Eloquent\Model;

interface UserInterface
{
    public function store (array $inputs);
    public function get_user_types();
    public function set_user_data($user_id, array $data);
    public function validate_inputs (array $inputs, $rules = null, $messages = null);
}
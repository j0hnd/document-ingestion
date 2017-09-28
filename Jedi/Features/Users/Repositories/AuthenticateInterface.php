<?php
namespace Jedi\Features\Users\Repositories;

use Illuminate\Database\Eloquent\Model;

interface AuthenticateInterface
{
    public function authenticate(array $inputs);
    public function validate_inputs(array $inputs);
}
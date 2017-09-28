<?php
namespace Jedi\Controllers;

use App\Http\Controllers\Controller;
use Auth;

class BaseController extends Controller {

    protected $views;

    public function __construct()
    {

    }

    public function get_auth_user_id()
    {
        return Auth::check() ? Auth::id() : null;
    }
}
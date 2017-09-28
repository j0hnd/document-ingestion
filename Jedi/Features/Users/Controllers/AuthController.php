<?php
namespace Jedi\Features\Users\Controllers;

use Jedi\Controllers\BaseController;
use Jedi\Features\Users\Repositories\AuthenticateInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Cookie, Redirect, Response, Input, URL;


class AuthController extends BaseController
{
    protected $auth;


    public function __construct(AuthenticateInterface $auth)
    {
        parent::__construct();

        $this->auth = $auth;
    }

    public function index(Request $request)
    {
        $this->views['title'] = 'Login';
        if (Auth::check())
        {
            return Redirect::intended('queue');
        }
        return view('users::signin.index')->with($this->views);
    }

    public function authenticate_login(Request $request)
    {
        $redirect = ['status' => false, 'error' => 'Can\'t authenticate user'];

        if ($request->isMethod('POST')) {
            $authenticate = $this->auth->authenticate(Input::except('_token'));

            if($authenticate['status']) {
                #return Redirect::intended('queue');
                $redirect = ['status' => true, 'url' => url('/queue')];
            } else {
                $redirect['error'] = 'Invalid Email Address and/or Password';
            }

            #return Redirect::back()->withErrors(['errors' => 'Invalid Email/Password']);
        }

        return $redirect;
    }

    public function forgot_password()
    {
        $this->views['title'] = 'Forgot Password';

        return view('users::forgot_password.index')->with($this->views);
    }

    public function process_forgot_password(Request $request)
    {
        $response = ['status' => true, 'message' => 'Invalid request'];
        if ($request->isMethod('POST')) {
            $input = Input::except('_token');
//            dd($input);
            $response = $this->auth->forgot_password($input);
        }

        return Response::json($response);
    }
}
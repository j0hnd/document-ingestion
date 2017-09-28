<?php
namespace Jedi\Features\Users\Controllers;

use Jedi\Controllers\BaseController;
use Jedi\Features\Users\Repositories\UserInterface;
use Jedi\Features\Sites\Repositories\SitesInterface;

use Redirect, Request, Input;
use Response, Hash;


class UserController extends BaseController
{
    protected $userRepo;


    public function __construct (
        UserInterface $userRepo,
        SitesInterface $siteRepo
    )
    {

        $this->userRepo = $userRepo;
        $this->siteRepo = $siteRepo;

        parent::__construct();
    }

    public function index()
    {
        $this->views['title'] = 'Users List';
        $this->views['users'] = $this->userRepo->get_all_active_users('lastname');
        $this->views['user_types'] = $this->userRepo->get_user_types();

        return view('users::users.index')->with($this->views);
    }

    public function store()
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $user = Input::get('user');

        if (Request::ajax()) {
            if(Request::method('POST') && !isset($user['id'])) {
                $inputs = [
                    'firstname'        => $user['firstname'] ,
                    'lastname'         => $user['lastname'] ,
                    'email'            => $user['email'] ,
                    'password'         => $user['password'] ,
                    'confirm_password' => $user['confirm_password'],
                    'permissions'      => $user['permissions']
                ];

                $response = $this->userRepo->store($inputs);
            } elseif(Request::method('POST') || Request::method('PATCH') && isset($user['id'])) {
                $inputs = [
                    'firstname'     => $user['firstname'] ,
                    'lastname'      => $user['lastname']
                ];

                $response = $this->userRepo->update($user['id'] , $inputs);
            }
        }

        return Response::json($response);
    }

    public function update_user($user_id)
    {
        $user_info_obj = $this->userRepo->get_active_user($user_id);
        $user_info     = null;

        if ($user_info_obj->count()) {
            $user_info = $user_info_obj->first();
        }

        $this->views['title'] = 'User Information';
        $this->views['user']  = $user_info;

        $this->views['permissions'] = $this->siteRepo->get_permissions();
        $this->views['sites'] = $this->siteRepo->get_sites();
        //user sites
        $this->views['user_sites'] = $this->siteRepo->get_user_sites($user_id);
        $this->views['user_permissions'] = $this->siteRepo->get_user_permissions();
        //end user sites
        return view('users::users.edit')->with($this->views);
    }

    public function disable_user($user_id)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        if (Request::method('POST')) {
            $response = $this->userRepo->set_user_data($user_id, ['is_active' => Input::get('disable')]);
        }

        return Response::json($response);
    }

    public function reset_password($user_id)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $data = [
            'password'         => Input::get('password'),
            'confirm_password' => Input::get('confirm_password')
        ];

        $rules = [
            'password'         => 'required|min:8|max:16',
            'confirm_password' => 'required|same:password'
        ];

        if (Request::method('POST')) {
            $response = $this->userRepo->validate_inputs($data, $rules);

            if ($response['status']) {
                // hash password
                $data = ['password' => Hash::make(Input::get('password'))];
                unset($data['confirm_password']);

                $response = $this->userRepo->set_user_data($user_id, $data);
            }
        }

        return Response::json($response);
    }

//    public function show($id)
//    {
//        $this->views['title'] = 'Update User';
//        $this->views['user']  = $this->user->find($id);
//
//        return view('users::show')->with($this->views);
//    }
//
//    public function edit($id)
//    {
//        $this->views['title'] = 'Update User';
//        $this->views['user']  = $this->user->find($id);
//
//        return view('users::edit')->with($this->views);
//    }
//
//    public function update(){}
//
//    public function destroy($id)
//    {
//        $this->user->delete($id);
//
//        return Redirect::to('users')->with(['message' => 'user has been deleted']);
//    }

}
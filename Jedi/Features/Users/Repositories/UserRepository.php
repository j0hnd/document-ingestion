<?php
namespace Jedi\Features\Users\Repositories;

use Illuminate\Database\QueryException;
use Mockery\CountValidator\Exception;
use Jedi\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Model;

use Jedi\Repositories\ValidatorInterface;
use Hash, DB;

use Jedi\Models\UserTypesModel;
use Jedi\Models\PermissionsModel;


class UserRepository extends EloquentRepository implements UserInterface
{

    protected $validator;

    protected $rules = [
        'firstname'        => 'required',
        'lastname'         => 'required',
        'email'            => 'required|email|unique:users',
        'password'         => 'required|min:8|max:16',
        'confirm_password' => 'required|same:password'
    ];

    protected $messages = [
        'required' => 'Please enter your :attribute',
    ];


    public function __construct(Model $model, ValidatorInterface $validator)
    {
        parent::__construct($model);

        $this->validator = $validator;
    }

    public function store (array $inputs)
    {
        try {
            $response = $this->validate_inputs($inputs);

            if($response['status']) {
                DB::beginTransaction();

                $this->model->firstname = $inputs['firstname'];
                $this->model->lastname  = $inputs['lastname'];
                $this->model->email     = $inputs['email'];
                $this->model->password  = Hash::make($inputs['password']);
                $this->model->user_type_id = $inputs['permissions'];
                $this->model->is_active    = 1;

                if ($this->model->save()) {
                    $response = [
                        'status'  => true,
                        'message' => 'User has been successfully Added.'
                    ];

                    DB::commit();
                }
            }
        } catch(Exception $e) {
            $response = [
                'status'  => false ,
                'message' => $this->parse_sql_error_message($e->getMessage()),
            ];

            DB::rollback();
        } catch (QueryException $qe) {
            $response = [
                'status'  => false ,
                'message' => $this->parse_sql_error_message($qe->getMessage())
            ];

            DB::rollback();
        }

        return $response;
    }

    public function get_user_types()
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        $user_types = null;

        try {
            $user_types_obj = UserTypesModel::where('is_active', 1)->orderBy('type_name', 'ASC');

            if ($user_types_obj->count()) {
                foreach ($user_types_obj->get() as $types) {
                    $user_types[$types->id] = $types->type_name;
                }

                $response = ['status' => true, 'data' => $user_types];
            }

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function set_user_data($user_id, array $data)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {

            if ($this->update($user_id, $data)) {
                $response = ['status' => true, 'message' => 'User information has been updated'];
            }

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return$response;
    }

    public function validate_inputs (array $inputs, $rules = null, $messages = null)
    {
        $rules    = is_null($rules) ? $this->rules : $rules;
        $messages = is_null($messages) ? $this->messages : $messages;

        $this->validator->with($inputs);
        $this->validator->rules($rules);
        $this->validator->messages($messages);

        if ($this->validator->passes()) {
            $status  = true;
            $errors  = null;
        } else {
            $status = false;
            $errors = $this->validator->errors();
        }

        return ['status' => $status , 'message' => $errors];
    }

}
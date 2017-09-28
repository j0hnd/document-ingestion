<?php
namespace Jedi\Features\Test\Repositories;

use Jedi\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Model;
use Jedi\Repositories\ValidatorInterface;

use Jedi\Features\Test\Models\TestModel;
use Jedi\Features\Test\Models\RegisterModel;

class RegisterRepository extends EloquentRepository implements RegisterInterface
{
    protected $validator;

    protected $rules = [];

    protected $messages = [];

    
    public function __construct(Model $model, ValidatorInterface $validator)
    {
        parent::__construct($model);

        $this->validator = $validator;
    }

    public function foobar()
    {
        return 'Registration';
    }

    public function save($data)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {

            if (strlen($data['first_name']) > 0) {
                $test_obj = new RegisterModel();
                $test_obj->firstname = $data['first_name'];
                $test_obj->lastname = $data['last_name'];
                $test_obj->username = $data['username'];
                $test_obj->password = $data['password'];
                $test_obj->account_type = $data['account_type'];
                $test_obj->gender = $data['gender'];
                $test_obj->college = $data['college'];
                $test_obj->address = $data['address'];

                if ($test_obj->save()) {
                    $response = ['status' => true, 'message' => 'Saved'];
                }
            }

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }
}
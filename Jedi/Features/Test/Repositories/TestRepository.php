<?php
namespace Jedi\Features\Test\Repositories;

use Jedi\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Model;
use Jedi\Repositories\ValidatorInterface;

use Jedi\Features\Test\Models\TestModel;



class TestRepository extends EloquentRepository implements TestInterface
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
        return 'Name';
    }

    public function save($data)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {

            if (strlen($data['fullname']) > 0) {
                $test_obj = new TestModel();
                $test_obj->fullname = $data['fullname'];

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
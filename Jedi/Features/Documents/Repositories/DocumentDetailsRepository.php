<?php
namespace Jedi\Features\Documents\Repositories;

use Jedi\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Model;
use Jedi\Repositories\ValidatorInterface;

use DB;


class DocumentDetailsRepository extends EloquentRepository implements DocumentDetailsInterface
{
    protected $validator;

    protected $rules = [
        'document_name' => 'required',
        'status'        => 'required'
    ];

    protected $messages = [
        'required' => 'This :attribute is a required field'
    ];


    public function __construct(Model $model, ValidatorInterface $validator)
    {
        parent::__construct($model);

        $this->validator = $validator;
    }

    public function update_document($document_id, $data)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {

            $result = $this->update($document_id, $data);

            if ($result) {
                $response = ['status' => true, 'message' => 'Document updated'];
            }

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function validate_inputs (array $inputs)
    {
        $this->validator->with($inputs);
        $this->validator->rules($this->rules);
        $this->validator->messages($this->messages);

        if ($this->validator->passes()) {
            $status  = true;
            $errors  = null;
        } else {
            $status = false;
            $errors = $this->validator->errors()->all();
        }

        return ['status' => $status , 'errors' => $errors];
    }
}
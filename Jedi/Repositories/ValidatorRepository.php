<?php
namespace Jedi\Repositories;

use Jedi\Repositories\ValidatorInterface;
use Validator;

class ValidatorRepository implements ValidatorInterface{
    /**
     * Validation data key => value array
     *
     * @var Array
     */
    protected $data = array();

    /**
     * Validation errors
     *
     * @var Array
     */
    protected $errors = array();

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array();

    /**
     * Custom validation messages
     *
     * @var Array
     */
    protected $messages = array();

    /**
     * Set data to validate
     *
     * @return \Impl\Service\Validation\AbstractLaravelValidation
     */
    public function with(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Validation passes or fails
     *
     * @return Boolean
     */
    public function passes()
    {
        $validator = Validator::make(
            $this->data,
            $this->rules,
            $this->messages
        );

        if ($validator->fails()) {
            $this->errors = $validator->messages();

            return false;
        } else {
            return true;
        }
    }

    /**
     * Return messages, if any
     *
     * @return array
     */
    public function messages(array $messages)
    {
        return $this->messages = $messages;
    }

    /**
     * Return rules, if any
     *
     * @return array
     */
    public function rules(array $rules)
    {
        return $this->rules = $rules;
    }

    /**
     * Return errors, if any
     *
     * @return array
     */
    public function errors() {
        return $this->errors;
    }
}
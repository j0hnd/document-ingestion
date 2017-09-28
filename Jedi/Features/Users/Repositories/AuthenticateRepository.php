<?php
namespace Jedi\Features\Users\Repositories;

use App\User;
use Mockery\CountValidator\Exception;
use Jedi\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use Jedi\Repositories\ValidatorInterface;
use Hash;


class AuthenticateRepository extends EloquentRepository implements AuthenticateInterface
{
    protected $validator;

    protected $rules = [
        'email'    => 'required'
    ];

    protected $messages = [
        'required' => 'Please enter your email/username.',
    ];


    public function __construct(Model $model , ValidatorInterface $validator)
    {
        parent::__construct($model);

        $this->validator = $validator;
    }

    public function authenticate(array $inputs)
    {
        $validated = ['status' => false];

        try {
            $credentials = [
                'email'     => $inputs['_email'],
                'password'  => $inputs['_password'],
                'is_active' => 1
            ];


            if (Auth::viaRemember()) {
                $validated['status'] = true;
            } else {
                $remember_me = isset($inputs['remember_me']) ? true : false;

                if (Auth::attempt($credentials, $remember_me)) {
                    $validated['status'] = true;
                } else {
                    $validated = ['status' => false, 'error' => 'Authentication failed'];
                }
            }


        } catch (\Exception $e) {
            $validated['message'] = $e->getMessage();
        }

        return $validated;
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
            $errors = $this->validator->errors();
        }

        return ['status' => $status , 'errors' => $errors];
    }

    public function forgot_password($attributes)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];
        try{
            //first check email if existing
            $checkEmail = User::where(['email' => $attributes['_email'], 'is_active' => 1]);
            if($checkEmail->count()){
                //if existing
                $user = $checkEmail->first();
                $attributes['newPwd'] = str_random(8);
                $user->password = Hash::make($attributes['newPwd']);
                if($user->save()){
                    $mail = Mail::send('emails.reset-password', ['attributes' => $attributes], function ($message) use ($user) {
                        $message->to($user->email , "{$user->first_name} {$user->last_name}");
                        $message->subject('Jedi Reset Password');
                        $message->from('noreply@jedi.dev', 'Do Not Reply');
                    });
                    if($mail) {
                        $response['status']  = true;
                        $response['message'] = "Your password has been updated, kindly check your email.";
                    } else {
                        $response['status']   = false;
                        $response['message']  = 'Failed to process your request , please try again.';
                    }
                }
            }else{
                //not existing
                $response['message'] = "Sorry, but the email address you entered does not exist on our records.";
            }
        }catch (Exception $e){
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}
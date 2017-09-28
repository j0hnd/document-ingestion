<?php namespace Jedi\Features\Test\Controllers;

use Jedi\Controllers\BaseController;
use Jedi\Features\Test\Repositories\TestInterface;
use Jedi\Features\Test\Repositories\RegisterInterface;
use Input, Request, Response;



class TestController extends BaseController
{
    protected $testRepo;


    public function __construct(RegisterInterface $testRepo)
    {
        $this->testRepo = $testRepo;

        parent::__construct();
    }


    public function index()
    {
    	$this->views['string'] = 'Hello World';

        return view('Test::home.index')->with($this->views);
    }

    public function foo($param)
    {
    	if ($param == 1) {
			$val = 'Hello world';
    	} else {
			$val = 'foo bar';
    	}

    	$this->views['string'] = $val;

    	return view('Test::home.index')->with($this->views);	
    }

    public function form()
    {
    	$this->views['title'] = $this->testRepo->foobar();
        return view('Test::home.form')->with($this->views);
    }

    public function process_form()
    {
    	$response = ['status' => false, 'message' => 'Invalid request'];

    	if (Request::ajax()) {

			if (Request::isMethod('POST')) {

				$form_data = Input::get('data');
				parse_str($form_data, $form);

                $data['fullname'] = $form['name'];
                $response = $this->testRepo->save($data);				
			}
    	}
    	return Response::json($response);
    }

    public function register()
    {
        $this->views['title'] = $this->testRepo->foobar();
        return view('Test::home.register')->with($this->views);
    }

    public function process_register()
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        if (Request::ajax()) {

            if (Request::isMethod('POST')) {

                $form_data = Input::get('data');
                parse_str($form_data, $form);

                $data['first_name'] = $form['first_name'];
                $data['last_name'] = $form['last_name'];
                $data['username'] = $form['username'];
                $data['password'] = $form['password'];
                $data['account_type'] = $form['account_type'];
                $data['gender'] = $form['gender'];
                $data['college'] = $form['college'];
                $data['address'] = $form['address'];
                
                $response = $this->testRepo->save($data);               
            }
        }

        return Response::json($response);
    }

}
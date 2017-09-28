<?php

Route::group(['namespace' => 'Jedi\Features\Users\Controllers'] , function() {
    Route::get('/sign-in' , [
        'as'    => 'route.signin' ,
        'uses'  => 'AuthController@index'
    ]);
    Route::get('/forgot-password' , [
        'as'    => 'route.forgot-password' ,
        'uses'  => 'AuthController@forgot_password'
    ]);

    Route::get('/', function () {
        if (Auth::check()) {
            return redirect('/queue');
        } else {
            return redirect('/sign-in');
        }
    });

    Route::get('/logout', function () {
        Auth::logout();
        Session::flush();

        return Redirect::to('/');
    });

    Route::post('/authenticate/signin' , 'AuthController@authenticate_login');
    Route::post('/user/forgotpassword' , 'AuthController@process_forgot_password');
});

Route::group(['middleware' => 'auth', 'namespace' => 'Jedi\Features\Users\Controllers'] , function() {
    Route::get('/users', 'UserController@index');
    Route::get('/users/{id}/edit', 'UserController@update_user');

    Route::post('/user/{id}/disable', 'UserController@disable_user');
    Route::post('/user/{id}/resetpwd', 'UserController@reset_password');
    Route::post('/users/save', 'UserController@store');

    Route::resource('users', 'UserController');
});
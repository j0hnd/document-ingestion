<?php

Route::group(['middleware' => 'auth', 'namespace' => 'Jedi\Features\Sites\Controllers'] , function() {
    Route::get('/templates', 'SiteController@index');
    Route::get('/templates/add', 'SitesController@site_add');
    Route::get('/template/{id}/read', 'SitesController@read_template');

    Route::post('/template/save', 'SitesController@site_save');
    Route::post('/template/{id}/delete', 'SitesController@site_delete');

//    Route::get('/users/{id}/edit', 'UserController@update_user');
//
//    Route::post('/user/{id}/disable', 'UserController@disable_user');
//    Route::post('/user/{id}/resetpwd', 'UserController@reset_password');
//    Route::post('/users/save', 'UserController@store');

    Route::match(['get', 'post'], '/template/{id}/edit', 'SitesController@site_edit');
    Route::resource('templates', 'SitesController');

    Route::post('/user/save_user_sites', 'SitesController@save_user_sites');
    Route::put('/user/save_user_sites', 'SitesController@save_user_sites');
    Route::get('/user/site/load/{id}', 'SitesController@load_user_site');
    Route::get('/user/site/get/', 'SitesController@load_user_add_site');
    Route::post('/user/site/delete', 'SitesController@delete_user_site');
});
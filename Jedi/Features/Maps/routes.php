<?php
Route::group(['namespace' => 'Jedi\Features\Maps\Controllers'] , function() {

   Route::get('/maps', 'MapsController@index');
   

   Route::post('/upload','MapsController@upload');
   Route::post('/submit-map','MapsController@submit_map');
   Route::post('/delete-map','MapsController@delete_map');
   Route::post('/update-modal','MapsController@update_modal');
   Route::post('/view-xml','MapsController@view_xml');
   Route::post('/view-xml-window','MapsController@view_xml_window');
   Route::post('/view-all-data','MapsController@view_all_data');
   Route::post('/create-name','MapsController@create_fieldname');
   Route::post('/delete-fieldname','MapsController@delete_fieldname');
   Route::post('/edit-fieldname','MapsController@edit_fieldname');
   Route::post('/add-fieldname','MapsController@add_fieldname');
   Route::post('/remove-fieldname','MapsController@remove_fieldname');
   Route::post('/get-selected','MapsController@get_selected');
   Route::post('/remove-header','MapsController@remove_header');
   Route::post('/refresh','MapsController@refresh');
   Route::post('/zip','MapsController@zip');
   Route::post('/download','MapsController@download');
   Route::match(['get', 'post'], '/test','MapsController@test');


});
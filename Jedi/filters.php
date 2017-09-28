<?php

Route::filter('auth', function() {
    if (!Sentinel::check()) return Redirect::to('/sign-in');
});

Route::filter('islogin', function() {
    if (Sentinel::check()) return Redirect::intended('');
});
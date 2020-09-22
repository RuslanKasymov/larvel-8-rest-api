<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;

Route::get('/user', function (Request $request) {
    return response('', Response::HTTP_NO_CONTENT);
});

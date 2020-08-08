<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('upload', 'UploadController@upload');

Route::post('text_upload', 'UploadController@video_watermark_text');

Route::post('text_upload_animate', 'UploadController@video_watermark_text_animate');

Route::post('merge_video', 'UploadController@merge_video_validation');

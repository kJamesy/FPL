<?php

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

Route::get('/', ['as' => 'guest.home', function () { return redirect(route('leagues.index')); }]);
Route::redirect('home', route('guest.home'));
Route::redirect('guest', route('guest.home'));

// Player Details
// https://fantasy.premierleague.com/drf/entry/99486
// League Details
// https://fantasy.premierleague.com/drf/leagues-classic-standings/28266
// Player Scores
// https://fantasy.premierleague.com/drf/entry/99486/event/8/picks

Route::group(['prefix' => 'lab'], function() {
    Route::get('/', function() {
        // 28266
        // 930895
        // 24813
        // 428936
        // 360101
        // 695687
    });

    Route::get('worker', function() {
//        return exec("php " . base_path() . "/artisan supervise:queue-worker");
		return \Illuminate\Support\Facades\Artisan::call('supervise:queue-worker');
    });
});

/**
 * Admin Routes
 */
Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
    Route::group(['namespace' => 'Auth'], function() {
        Route::get('register', ['as' => 'admin.auth.show_registration', 'uses' => 'RegisterController@showRegistrationForm']);
        Route::post('register', ['as' => 'admin.auth.store_registration', 'uses' => 'RegisterController@register']);
        Route::get('login', ['as' => 'admin.auth.show_login', 'uses' => 'LoginController@showLoginForm']);
        Route::post('login', ['as' => 'admin.auth.process_login', 'uses' => 'LoginController@login']);
        Route::get('password/reset', ['as' => 'admin.auth.show_password_reset', 'uses' => 'ForgotPasswordController@showLinkRequestForm']);
        Route::post('password/email', ['as' => 'admin.auth.send_password_reset_email', 'uses' => 'ForgotPasswordController@sendResetLinkEmail']);
        Route::get('password/reset/{token}', ['as' => 'admin.auth.show_password_reset_form', 'uses' => 'ResetPasswordController@showResetForm']);
        Route::post('password/reset', ['as' => 'admin.auth.process_password_reset_form', 'uses' => 'ResetPasswordController@reset']);
        Route::post('logout', ['as' => 'admin.auth.post_logout', 'uses' => 'LoginController@logout']);
        Route::get('logout', ['as' => 'admin.auth.get_logout', 'uses' => 'LoginController@logout']);
    });

    Route::group(['middleware' => ['auth']], function() {
        Route::group(['middleware' => ['active']], function() {
            Route::get('/', ['as' => 'admin.home', 'uses' => 'AdminController@index']);

            if ( ! request()->ajax() ) {
                Route::get('settings/{vue?}', 'AdminController@index');
                Route::get('users/export', 'UserController@export');
                Route::get('users/{vue?}', 'UserController@index');
            }

            Route::resource('settings', 'AdminController');
            Route::put('users/{option}/quick-update', 'UserController@quickUpdate');
            Route::resource('users', 'UserController');
        });

        Route::get('inactive', ['as' => 'admin.inactive', 'middleware' => 'inactive', function () { return view('admin.inactive'); }]);
    });

    Route::get('login-helper', ['as' => 'login', function () { return redirect(route('admin.auth.show_login')); }]);
});

Route::group(['prefix' => 'guest', 'namespace' => 'Guest', 'middleware' => ['auto_login_guest']], function() {

    if ( ! request()->ajax() ) {
	    Route::get('leagues/export', 'LeagueController@export');
        Route::get('leagues/{vue?}', 'LeagueController@index');
    }

    Route::resource('leagues', 'LeagueController');
});

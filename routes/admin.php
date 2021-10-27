<?php
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;


Route::prefix('admin')->name('admin.')->group(function () {

    Route::view('/login','auth.admin.login')->name('login') ->middleware( 'guest:admin',);

    $limiter = config('fortify.limiters.login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            'guest:admin',
            $limiter ? 'throttle:'.$limiter : null,
        ]));

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout')->middleware('auth:admin');

    Route::get('/home', function () {
        return view('adminHome');
    })->name('home')->middleware('auth:admin');


});


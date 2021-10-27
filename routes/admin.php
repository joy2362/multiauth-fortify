<?php
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;


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
    })->name('home')->middleware('auth:admin','verified');

    Route::view('/register','auth.admin.registation')->middleware( 'guest:admin')
        ->name('register');;

    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('guest:admin');

    // Email Verification...
    Route::view('/email/verify','auth.admin.verify')->middleware( 'auth:admin')
        ->name('verification.notice');

    $verificationLimiter = config('fortify.limiters.verification', '6,1');

    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['auth:admin', 'signed', 'throttle:'.$verificationLimiter])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware(['auth:admin', 'throttle:'.$verificationLimiter])
        ->name('verification.send');

    //forget password

    Route::view('/forgot-password' ,'auth.admin.password.email')
        ->middleware(['guest:admin'])
        ->name('password.request');

    Route::view('/reset-password/{token}' ,'auth.admin.password.reset')
        ->middleware(['guest:admin'])
        ->name('password.reset');


    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware(['guest:admin'])
        ->name('password.email');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware(['guest:admin'])
        ->name('password.update');

});


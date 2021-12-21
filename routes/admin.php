<?php
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;


Route::prefix('admin')->name('admin.')->group(function () {

    Route::view('/login','auth.admin.login')
        ->middleware( 'guest:admin',)
        ->name('login');

    $limiter = config('fortify.limiters.login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            'guest:admin',
            $limiter ? 'throttle:'.$limiter : null,
        ]));
    //logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout')->middleware('auth:admin');
    //dashboard
    Route::get('/home', function () {
        return view('adminHome');
    })->name('home')->middleware('auth:admin','verified:admin.verification.notice');

        //registration
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


    //update user information
    Route::get('/profile/edit', function () {
        return view('adminProfile');
    })->middleware('auth:admin')->name('profile');

    Route::put('/profile-information', [ProfileInformationController::class, 'update'])
        ->middleware(['auth:admin'])
        ->name('profile-information.update');



    // Passwords...
    Route::view('password/change','adminPassword_change')
        ->middleware('auth:admin')
        ->name('password.change');

    Route::put('/password', [PasswordController::class, 'update'])
        ->middleware(['auth:'.config('fortify.guard')])
        ->name('password.update.put');


    //confirm password
    Route::view('/confirm-password','auth.admin.password.confirm')
        ->middleware('auth:admin')
        ->name('password.confirm');


    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware(['auth:admin']);


    Route::view('/two-factor-challenge','auth.admin.twoFactorChallenge')
        ->middleware('guest:admin',)
        ->name('two-factor.login');

    $twoFactorLimiter = config('fortify.limiters.two-factor');
    Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            'guest:admin',
            $twoFactorLimiter ? 'throttle:'.$twoFactorLimiter : null,
        ]));

    Route::post('/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
        ->middleware(['auth:admin','password.confirm:admin.password.confirm' ])
        ->name('two-factor.enable');

    Route::delete('/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
        ->middleware(['auth:admin','password.confirm:admin.password.confirm'])
        ->name('two-factor.disable');

    Route::get('/user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])
        ->middleware(['auth:admin', 'password.confirm'])
        ->name('two-factor.qr-code');

    Route::get('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
        ->middleware(['auth:admn', 'password.confirm'])
        ->name('two-factor.recovery-codes');

    Route::post('/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
        ->middleware(['auth:admin', 'password.confirm:admin.password.confirm']);
});


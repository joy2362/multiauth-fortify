<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\FailedTwoFactorLoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (request()->is('admin/*')){
            config() ->set('fortify.guard','admin');
            config() ->set('fortify.home','admin/home');
            config() ->set('fortify.passwords','admins');
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        Fortify::loginView(function () {
            return view('auth.user.login');
        });
        Fortify::registerView(function () {
            return view('auth.user.registation');
        });

        Fortify::requestPasswordResetLinkView(function(){
            return view('auth.user.password.email');
        });

        Fortify::confirmPasswordView(function(){
            return view('auth.user.password.confirm');
        });

        Fortify::resetPasswordView(function(){
            return view('auth.user.password.reset');
        });

        Fortify::verifyEmailView(function(){
            return view('auth.user.verify');
        });
        Fortify::twoFactorChallengeView(function(){
            return view('auth.user.twoFactorChallenge');
        });


        if (request()->is('admin/*')) {
            $this->app->singleton(
                \Laravel\Fortify\Contracts\PasswordResetResponse::class,
                \App\Http\Responses\PasswordResetResponse::class
            );

            $this->app->singleton(
                \Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse::class,
                \App\Http\Responses\FailedTwoFactorLoginResponse::class
            );

        }



    }
}

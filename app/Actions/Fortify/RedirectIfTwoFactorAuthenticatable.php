<?php

namespace App\Actions\Fortify;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as RedirectIfTwoFactorAuthenticatableContract;


class RedirectIfTwoFactorAuthenticatable extends RedirectIfTwoFactorAuthenticatableContract
{
    protected function twoFactorChallengeResponse($request, $user)
    {
        $request->session()->put([
            'login.id' => $user->getKey(),
            'login.remember' => $request->filled('remember'),
        ]);


        return $request->wantsJson()
            ? response()->json(['two_factor' => true])
            : redirect()->route('admin.two-factor.login');

    }
}

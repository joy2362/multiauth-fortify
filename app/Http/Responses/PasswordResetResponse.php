<?php
namespace App\Http\Responses;

use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
class PasswordResetResponse implements PasswordResetResponseContract
{
    public function toResponse($request)
    {
        //dd($request);
        return redirect(route('admin.login'));
    }
}

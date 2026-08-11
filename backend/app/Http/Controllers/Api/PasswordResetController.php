<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Notifications\ApiResetPasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    /**
     * Send a password reset token to the given email (API-appropriate, returns the token via email).
     *
     * Always answers with the same message to avoid leaking whether an email is registered.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink(
            ['email' => $request->validated('email')],
            function ($user, string $token) {
                $user->notify(new ApiResetPasswordNotification($token, $user->email));
            }
        );

        return response()->json(['message' => __('passwords.sent')]);
    }

    /**
     * Reset the user's password using a valid token.
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->validated(),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => null,
                ])->save();

                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __('passwords.failed'),
            ], 422);
        }

        return response()->json(['message' => __('passwords.reset')]);
    }
}

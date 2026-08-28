<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UnverifiedEmail
{
    /**
     * @return array<string, list<ValidationRule|string>>
     */
    public static function rules(User $user): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ];
    }

    public static function resend(User $user): void
    {
        AdminUserVerification::resend($user);
    }

    /**
     * @throws ValidationException
     */
    public static function update(User $user, string $email): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $validated = Validator::make(
            ['email' => $email],
            self::rules($user),
            [
                'email.unique' => __('onboarding.verify_email_taken'),
            ],
        )->validate();

        $normalizedEmail = $validated['email'];

        if (strcasecmp($normalizedEmail, $user->email) === 0) {
            return false;
        }

        $user->forceFill([
            'email' => $normalizedEmail,
        ])->save();

        $user->sendEmailVerificationNotification();

        return true;
    }
}

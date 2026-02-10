<?php

namespace App\Actions\Fortify;

use App\Models\Usuario;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function update(Usuario $user, array $input): void
    {
        Validator::make($input, [
            'nombre' => ['required', 'string', 'max:120'],
            'apellido' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('usuario')->ignore($user->id),
            ],
        ])->validateWithBag('updateProfileInformation');

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'nombre' => $input['nombre'],
                'apellido' => $input['apellido'],
                'email' => $input['email'],
            ])->save();
        }
    }

    protected function updateVerifiedUser(Usuario $user, array $input): void
    {
        $user->forceFill([
            'nombre' => $input['nombre'],
            'apellido' => $input['apellido'],
            'email' => $input['email'],
            'email_verificado' => false,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
<?php

namespace App\Services\Prosocio;

use App\Models\ProsocioAccount;
use App\Models\User;
use Laravel\Socialite\Two\User as ProviderUser;

class ProsocioAccountsService
{
    /**
     * Find or create user instance by provider user instance and provider name.
     *
     * @param ProviderUser $providerUser
     * @param string $provider
     *
     * @return User
     */
    public function findOrCreate(ProviderUser $providerUser, string $provider): User
    {
        $prosocioAccount = \App\ProsocioAccount::where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($prosocioAccount) {
            return $prosocioAccount->user;
        } else {
            $user = null;

            if ($email = $providerUser->getEmail()) {
                $user = User::where('email', $email)->first();
            }

            if (!$user) {
                $user = User::create([
                    'first_name' => $providerUser->getName(),
                    'last_name' => $providerUser->getName(),
                    'email' => $providerUser->getEmail(),
                ]);
            }

            $user->ProsocioAccounts()->create([
                'provider_id' => $providerUser->getId(),
                'provider_name' => $provider,
            ]);

            return $user;
        }
    }
}

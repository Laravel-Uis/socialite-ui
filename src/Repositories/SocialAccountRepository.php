<?php

namespace SocialiteUi\Repositories;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Socialite\Contracts\User as Socialite;
use SocialiteUi\Contracts\SocialAccount;
use SocialiteUi\Contracts\SocialAccountRepository as Contract;
use SocialiteUi\SocialiteUi;

/**
 * @phpstan-type TSocialAccount (Model&SocialAccount)|null
 *
 * @internal
 */
final class SocialAccountRepository implements Contract
{
    public function find(string $provider, string $providerId): (Model&SocialAccount)|null
    {
        /** @var ?TSocialAccount */
        return SocialiteUi::socialAccountModel()::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();
    }

    public function findForProviderAndEmail(string $provider, string $email): (Model&SocialAccount)|null
    {
        /** @var ?TSocialAccount */
        return SocialiteUi::socialAccountModel()::query()
            ->where('provider', $provider)
            ->where('email', $email)
            ->first();
    }

    public function create(Authenticatable $user, string $provider, Socialite $data): Model&SocialAccount
    {
        /** @var Model&SocialAccount */
        return SocialiteUi::socialAccountModel()::query()->forceCreate([
            'user_id' => $user->getAuthIdentifier(),
            'provider' => strtolower($provider),
            'provider_id' => $data->getId(),
            'name' => $data->getName(),
            'nickname' => $data->getNickname(),
            'email' => $data->getEmail(),
            'avatar' => $data->getAvatar(),
            'token' => $data->token ?? null,
            'secret' => $data->tokenSecret ?? null,
            'refresh_token' => $data->refreshToken ?? null,
            'expires_at' => property_exists($data, 'expiresIn') ? now()->addSeconds($data->expiresIn) : null,
        ]);
    }

    public function update(SocialAccount $original, Socialite $data): SocialAccount
    {
        $account = $this->findForProviderAndEmail($original->getProvider(), $original->getEmail());

        $account->forceFill([
            'provider_id' => $data->getId(),
            'name' => $data->getName(),
            'nickname' => $data->getNickname(),
            'email' => $data->getEmail(),
            'avatar' => $data->getAvatar(),
            'token' => $data->token ?? null,
            'secret' => $data->tokenSecret ?? null,
            'refresh_token' => $data->refreshToken ?? null,
            'expires_at' => property_exists($data, 'expiresIn') ? now()->addSeconds($data->expiresIn) : null,
        ]);

        $account->save();

        return $account;
    }
}

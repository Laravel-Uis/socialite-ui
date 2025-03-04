<?php

namespace SocialiteUi\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use SocialiteUi\SocialiteUi;

/**
 * @property Collection<int, \App\Models\SocialAccount> $socialAccounts
 */
trait HasSocialAccounts
{
    /**
     * Determine if the user owns the given social account.
     */
    public function ownsSocialAccount(mixed $socialAccount): bool
    {
        return $this->id == optional($socialAccount)->user_id;
    }

    /**
     * Determine if the user has a specific account type.
     */
    public function hasTokenFor(string $provider): bool
    {
        return $this->socialAccounts->contains('provider', Str::lower($provider));
    }

    /**
     * Attempt to retrieve the token for a given provider.
     */
    public function getTokenFor(string $provider, mixed $default = null): mixed
    {
        if ($this->hasTokenFor($provider)) {
            return $this->socialAccounts
                ->where('provider', Str::lower($provider))
                ->first()
                ->token;
        }

        return $default;
    }

    /**
     * Attempt to find a social account that belongs to the user,
     * for the given provider and ID.
     */
    public function getSocialiteUserFor(string $provider, string $id): mixed
    {
        return $this->socialAccounts
            ->where('provider', $provider)
            ->where('provider_id', $id)
            ->first();
    }

    /**
     * Get all the social accounts belonging to the user.
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialiteUi::socialAccountModel(), 'user_id', $this->getAuthIdentifierName());
    }
}

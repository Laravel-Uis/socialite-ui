<?php

namespace SocialiteUi\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use SocialiteUi\Contracts\SocialAccount;

class OAuthProviderLinkFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Authenticatable $user,
        public string $provider,
        public ?SocialAccount $socialAccount,
        public ?SocialiteUser $socialiteUser = null,
    ) {
        //
    }
}

<?php

namespace SocialiteUi\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Laravel\Socialite\Contracts\User as ProviderUser;

class OAuthLogin
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public mixed $user,
        public string $provider,
        public mixed $socialAccount,
        public ProviderUser $providerAccount,
    ) {
        //
    }
}

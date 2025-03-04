<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SocialiteUi\Enums\Provider;
use SocialiteUi\SocialiteUi;

class SocialiteUiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        SocialiteUi::promptOAuthLinkUsing(function (Provider $provider) {
            return to_route('confirm-link-account', [
                'provider' => $provider,
            ]);
        });
    }
}

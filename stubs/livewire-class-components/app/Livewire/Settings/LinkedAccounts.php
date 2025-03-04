<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use SocialiteUi\Enums\Provider;
use SocialiteUi\SocialiteUi;

class LinkedAccounts extends Component
{
    public function unlinkedProviders(): array
    {
        $socialAccounts = auth()->user()->socialAccounts
            ->pluck('provider')
            ->map(fn (Provider $provider) => $provider->name)
            ->all();

        return collect(SocialiteUi::providers())
            ->filter(function (Provider $provider) use ($socialAccounts) {
                return ! in_array($provider->name, $socialAccounts);
            })
            ->values()
            ->all();
    }
}

<?php

namespace App\Livewire\Settings;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use SocialiteUi\Events\SocialAccountDeleted;
use SocialiteUi\Providers;

class LinkedAccount extends Component
{
    public string $password = '';

    public SocialAccount $socialAccount;

    /**
     * Delete the currently authenticated user.
     */
    public function unlinkAccount(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $this->socialAccount->delete();

        event(new SocialAccountDeleted($this->socialAccount));

        Session::flash(
            'status',
            __(':provider account unlinked.', ['provider' => Providers::name($this->socialAccount->provider)])
        );

        $this->redirect(route('linked-accounts'), navigate: true);
    }
}

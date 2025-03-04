<?php

namespace App\Livewire\Settings;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UpdateAvatar extends Component
{
    public SocialAccount $socialAccount;

    public function updateAvatar(): void
    {
        Auth::user()->update([
            'avatar' => $this->socialAccount->avatar,
        ]);

        $this->reset();

        session()->flash('status', __('Avatar updated.'));

        $this->redirect(route('linked-accounts'), navigate: true);
    }
}

<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;
use SocialiteUi\SocialiteUi;

#[Layout('components.layouts.auth')]
class ConfirmLinkAccount extends Component
{
    public string $provider = '';

    public function mount(): void
    {
        $this->provider = SocialiteUi::provider(request()->string('provider'))->name;
    }
}

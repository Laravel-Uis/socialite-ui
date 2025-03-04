<?php

use App\Models\SocialAccount;
use Livewire\Volt\Component;

new class extends Component {
    public SocialAccount $socialAccount;

    public function submit(): void
    {
        auth()->user()->update([
            'avatar' => $this->socialAccount->avatar,
        ]);

        $this->dispatch('avatar-updated');
    }
}; ?>

<form wire:submit="submit">
    <flux:button variant="ghost" type="submit" class="cursor-pointer">
        <span class="text-sm">{{ __('Use Avatar') }}</span>
    </flux:button>
</form>

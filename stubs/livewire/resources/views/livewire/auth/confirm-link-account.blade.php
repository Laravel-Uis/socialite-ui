<?php

use SocialiteUi\Enums\Provider;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')]
class extends Component {
    public string $provider = '';
    public string $password = '';

    public function mount(): void
    {
        $this->provider = request()->enum('provider', Provider::class)?->name ?? request()->string('provider')->toString();
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header
            :title="__('Link :provider', ['provider' => Providers::name($provider)])"
            :description="__('Please confirm your password before linking your :provider account.', ['provider' => Providers::name($provider)])"
    />

    <form method="post" action="{{ route('oauth.confirm', ['provider' => $provider]) }}" class="flex flex-col gap-6">
        @csrf

        <!-- Password -->
        <flux:input
                wire:model="password"
                id="password"
                label="{{ __('Password') }}"
                type="password"
                name="password"
                autocomplete="new-password"
                placeholder="Password"
        />

        <flux:button variant="primary" type="submit" name="result" value="confirm" class="w-full">
            {{ __('Confirm') }}
        </flux:button>

        <flux:button variant="danger" type="submit" name="result" value="deny" class="w-full">
            {{ __('Deny') }}
        </flux:button>
    </form>
</div>

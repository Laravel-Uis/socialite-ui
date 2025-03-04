<?php

use App\Models\SocialAccount;
use Socialite\Events\SocialAccountDeleted;
use Socialite\Providers;
use Livewire\Volt\Component;

new class extends Component {
    public SocialAccount $socialAccount;

    public string $password = '';

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

        Session::flash('status', __(':provider account unlinked.', ['provider' => Providers::name($this->socialAccount->provider)]));

        $this->redirect(route('linked-accounts'), navigate: true);
    }
}; ?>

<div class="px-6 py-4 gap-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
    <div class="grid w-full md:grid-cols-2 items-center space-y-6 md:space-y-0">
        <div class="flex flex-row-reverse md:flex-row justify-between md:justify-start items-center gap-3">
            <x-socialite-provider-icon :provider="$socialAccount->provider" class="h-8 w-8"/>

            <div class="flex items-center justify-center gap-2">
                <flux:profile
                        :name="$socialAccount->name"
                        :avatar="$socialAccount->avatar"
                        :chevron="false"
                />
            </div>
        </div>

        <div class="flex flex-col md:flex-row items-center justify-between md:justify-end gap-3 md:gap-2">
            <livewire:settings.update-avatar :socialAccount="$socialAccount"/>

            @if(! auth()->user()->getAuthPassword())
                <flux:button variant="danger" :disabled="! auth()->user()->getAuthPassword()" class="w-full md:w-auto">
                    {{ __('Unlink') }}
                </flux:button>
            @else
                <flux:modal.trigger name="confirm-unlink-account-{{$socialAccount->id}}">
                    <flux:button variant="danger" x-data=""
                                 x-on:click.prevent="$dispatch('open-modal', 'confirm-unlink-account-{{$socialAccount->id}}')">
                        {{ __('Unlink') }}
                    </flux:button>
                </flux:modal.trigger>
            @endif
        </div>
    </div>

    <flux:modal name="confirm-unlink-account-{{$socialAccount->id}}" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="unlinkAccount" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Are you sure you want to unlink this account?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Once unlinked, you will no longer be able to sign in with this account.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" id="password" label="{{ __('Password') }}" type="password"
                        name="password"/>

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Unlink account') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout heading="{{ __('Linked Accounts') }}" subheading="{{ __('View and remove your currently linked accounts') }}">
        {{-- Linked accounts --}}
        <div class="space-y-6">
            <div class="space-y-4 rounded-lg border border-red-500/10 bg-red-500/5 py-3 px-4">
                <div class="text-red-500/80 dark:text-red-500/80">
                    <p class="text-sm">
                        {{ __('If you feel any of your social accounts have been compromised, you should disconnect them
                        immediately and change your password.') }}
                    </p>
                </div>
            </div>

            <!-- Session Status -->
            <x-auth-session-status :status="session('status')"/>

            @if(! auth()->user()->getAuthPassword() && auth()->user()->socialAccounts()->count() < 2)
                <div class="space-y-4 rounded-lg border border-red-500/10 bg-red-500/5 py-3 px-4">
                    <div class="text-red-500/80 dark:text-red-500/80">
                        <p class="text-xs">
                            {{ __('Set a password to unlink this account.') }}
                        </p>
                    </div>
                </div>
            @endif

            @foreach(auth()->user()->socialAccounts as $socialAccount)
                <livewire:settings.linked-account :socialAccount="$socialAccount"/>
            @endforeach
        </div>

        {{-- Unlinked accounts --}}
        <div class="space-y-6 mt-12">
            <div>
                <flux:heading>
                    {{  __('Link Account') }}
                </flux:heading>

                <flux:description>
                    {{ __('Link your account to any of the following services to enable additional login options.') }}
                </flux:description>
            </div>

            @session('socialite-ui.error')
                <div class="font-medium text-sm text-red-500/80">
                    {{ session('socialite-ui.error') }}
                </div>
            @endsession

            @if(! auth()->user()->getAuthPassword())
                <div class="space-y-4 rounded-lg border border-red-500/10 bg-red-500/5 py-3 px-4">
                    <div class="text-red-500/80 dark:text-red-500/80">
                        <p class="text-xs">
                            {{ __('Set a password to link new accounts.') }}
                        </p>
                    </div>
                </div>
            @endif

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->unlinkedProviders() as $provider)
                    <div class="flex flex-col space-y-6 md:space-y-4 rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
                        <div class="flex w-full justify-center py-2">
                            <x-socialite-provider-icon :provider="$provider" class="mx-auto h-8 w-8 md:mx-0"/>
                        </div>

                        <div class="flex w-full justify-end">
                            <flux:button variant="filled" class="w-full" :disabled="!auth()->user()->getAuthPassword()">
                                <a href="{{ route('oauth.redirect', ['provider' => $provider->name ]) }}"
                                   class="flex items-center justify-center w-full h-full">
                                    {{ __("Link $provider->name") }}
                                </a>
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-settings.layout>
</section>

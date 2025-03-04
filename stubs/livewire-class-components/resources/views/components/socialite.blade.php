<div {{ $attributes->merge(['class' => 'grid space-y-6']) }}>
    <div class="inline-flex items-center w-full">
        <flux:separator class="shrink"/>
        <span class="text-muted-foreground text-center text-sm mx-3">OR</span>
        <flux:separator class="shrink"/>
    </div>

    @if(session()->has('socialite-ui.error'))
        <div class="text-red-500/80">
            {{ session()->get('socialite-ui.error') }}
        </div>
    @endif

    <div class="grid grid-cols-4 gap-3">
        @foreach(\SocialiteUi\SocialiteUi::providers() as $provider)
            <a
                    key={provider.id}
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm h-9 px-3 py-2 border hover:border-black transition"
                    href="{{ route('oauth.redirect', $provider->name ) }}"
            >
                <x-socialite-provider-icon :provider="$provider" class="w-5 h-5 mx-2"/>
            </a>
        @endforeach
    </div>
</div>

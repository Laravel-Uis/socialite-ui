@php use \SocialiteUi\Enums\Provider; @endphp
@props([
    'provider' => null,
    'class' => null,
])

@switch($provider)
    @case(Provider::Bitbucket)
        <x-socialite-provider-icons.bitbucket class="{{ $class }}" />
        @break
    @case(Provider::Facebook)
        <x-socialite-provider-icons.facebook class="{{ $class }}" />
        @break
    @case(Provider::Github)
        <x-socialite-provider-icons.github class="{{ $class }}" />
        @break
    @case(Provider::Gitlab)
        <x-socialite-provider-icons.gitlab class="{{ $class }}" />
        @break
    @case(Provider::Google)
        <x-socialite-provider-icons.google class="{{ $class }}" />
        @break
    @case(Provider::LinkedIn)
    @case(Provider::LinkedInOpenId)
        <x-socialite-provider-icons.linkedin class="{{ $class }}" />
        @break
    @case(Provider::Slack)
    @case(Provider::SlackOpenId)
        <x-socialite-provider-icons.slack class="{{ $class }}" />
        @break
    @case(Provider::Twitch)
        <x-socialite-provider-icons.twitch class="{{ $class }}" />
        @break
    @case(Provider::Twitter)
    @case(Provider::TwitterOAuth2)
        <x-socialite-provider-icons.twitter class="{{ $class }}" />
        @break
    @case(Provider::X)
        <x-socialite-provider-icons.x class="{{ $class }}" />
        @break
    @default
        <div>{{ $provider->name }}</div>
@endswitch

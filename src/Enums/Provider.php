<?php

namespace SocialiteUi\Enums;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;

/** @implements Arrayable<string, string> */
enum Provider: string implements Arrayable, JsonSerializable
{
    case Bitbucket = 'bitbucket';
    case Facebook = 'facebook';
    case Github = 'github';
    case Gitlab = 'gitlab';
    case Google = 'google';
    case LinkedIn = 'linkedin';
    case LinkedInOpenId = 'linkedin-openid';
    case Slack = 'slack';
    case SlackOpenId = 'slack-openid';
    case Twitch = 'twitch';
    case Twitter = 'twitter';
    case TwitterOAuth2 = 'twitter-oauth-2';
    case X = 'x';
    case XOAuth2 = 'x-oauth-2';

    /**
     * Get the name of the provider.
     */
    public function name(): string
    {
        return match ($this) {
            self::Github => 'GitHub',
            self::Twitter, self::TwitterOAuth2 => 'Twitter',
            self::LinkedIn, self::LinkedInOpenId => 'LinkedIn',
            self::Slack, self::SlackOpenId => 'Slack',
            self::X, self::XOAuth2 => 'X',
            default => Str::of($this->value)->headline(),
        };
    }

    /**
     * Get the provider's details as an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->name(),
        ];
    }

    /**
     * Convert the object into something JSON serializable.
     */
    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

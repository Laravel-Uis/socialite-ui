<?php

namespace SocialiteUi;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use SocialiteUi\Contracts\SocialAccount as SocialAccountContract;

abstract class SocialAccount extends Model implements SocialAccountContract
{
    use HasTimestamps;

    public function getUserId(): string|int
    {
        return $this->user_id;
    }

    public function getProvider(): string
    {
        return strtolower($this->provider->value);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function getExpiry(): ?DateTimeInterface
    {
        return $this->expires_at;
    }
}

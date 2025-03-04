<?php

declare(strict_types=1);

namespace SocialiteUi\Tests\Fixtures;

use DateTimeInterface;
use SocialiteUi\Contracts\SocialAccount;

final class SocialAccountTemplate implements SocialAccount
{
    public string $refresh_token = 'refresh-token';

    public function getUserId(): string|int
    {
        return 1;
    }

    public function getProvider(): string
    {
        return 'github';
    }

    public function getName(): string
    {
        return 'Joel Butcher';
    }

    public function getEmail(): string
    {
        return 'joel@socialite-ui.dev';
    }

    public function getToken(): string
    {
        return 'token';
    }

    public function getSecret(): ?string
    {
        return null;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function getExpiry(): ?DateTimeInterface
    {
        return null;
    }
}

<?php

namespace SocialiteUi\Contracts;

use DateTimeInterface;
use SocialiteUi\Enums\Provider;

/**
 * @property string|int $user_id
 * @property string $name
 * @property string $email
 * @property string $avatar
 * @property Provider $provider
 * @property string $provider_id
 * @property string $token
 * @property ?string $secret
 * @property ?string $refresh_token
 * @property ?DateTimeInterface $expires_at
 */
interface SocialAccount
{
    public function getUserId(): string|int;

    public function getProvider(): string;

    public function getName(): string;

    public function getEmail(): string;

    public function getToken(): string;

    public function getSecret(): ?string;

    public function getRefreshToken(): ?string;

    public function getExpiry(): ?DateTimeInterface;
}

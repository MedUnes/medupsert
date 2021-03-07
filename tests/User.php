<?php

declare(strict_types=1);

namespace Medupsert\Tests;

use DateTime;

class User
{
    private string $username;
    private string $email;
    private DateTime $subscribed;

    public function __construct(string $username, string $email, DateTime $subscribed)
    {
        $this->username = $username;
        $this->email = $email;
        $this->subscribed = $subscribed;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSubscribed(): DateTime
    {
        return $this->subscribed;
    }
}

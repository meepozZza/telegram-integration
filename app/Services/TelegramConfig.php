<?php

declare(strict_types=1);

namespace App\Services;

class TelegramConfig
{
    public string $token;
    public array $chats;

    public function __construct()
    {
        $this->token = config('services.telegram.token');
        $this->chats = explode(',',config('services.telegram.chats'));
    }
}

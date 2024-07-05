<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TelegramSendMessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'message.text' => 'required|string',
        ];
    }
}

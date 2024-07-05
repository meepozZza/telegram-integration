<?php

declare(strict_types=1);

use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'telegram'], function ($group) {
    $group->get('{login_id}/history', [TelegramController::class, 'history']);
    $group->post('{login_id}/send-message', [TelegramController::class, 'sendMessage']);
    $group->post('webhook', [TelegramController::class, 'webhook']);
});

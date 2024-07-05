<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SocketClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TelegramHistory;
use App\Services\TelegramConfig;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Requests\TelegramSendMessageRequest;

class TelegramController
{
    public function history(string $loginId): JsonResource
    {
        return JsonResource::collection(
            collect(TelegramHistory::query()
                ->where('login_id', $loginId)
                ->orderBy('created_at')
                ->get())->map(function ($i) {
                return [
                    'id' => $i->id,
                    'initiator' => $i->initiator,
                    'message' => $i->initiator === 'telegram' ? $i->message['text'] : $this->messageClear($i->message['text']),
                    'created_at' => $i->created_at
                ];
            })
        );
    }

    private function messageClear(string $msg): string
    {
        $msg = strip_tags($msg);
        $msg = Str::replace(Arr::first(explode(":", $msg)) . ":", "", $msg);

        return trim($msg);
    }

    public function sendMessage(TelegramSendMessageRequest $request, TelegramConfig $config, string $loginId): Response
    {
        $messageText = "<b>User {$loginId}:</b> " . $request->validated('message.text');

        foreach ($config->chats as $chat) {
            $result = Http::post("https://api.telegram.org/bot{$config->token}/sendMessage?chat_id={$chat}&text={$messageText}&parse_mode=html");

            if ($result->ok()) {
                TelegramHistory::query()
                    ->create([
                        'initiator' => 'client',
                        'login_id' => $loginId,
                        'message' => $result->json('result'),
                    ]);
            }
        }

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function webhook(Request $request)
    {
        $request = $request->all();

        $message = $request['message'];
        $replyToMessageId = $message['reply_to_message']['message_id'] ?? null;

        if (!$replyToMessageId) {
            return response()->noContent();
        }

        $lastTelegramHistory = TelegramHistory::query()
            ->whereJsonContains('message->message_id', $replyToMessageId)
            ->orderByDesc('created_at')
            ->first();

        $loginId = $lastTelegramHistory->login_id;

        if (!$loginId) {
            return response()->noContent();
        }

        broadcast(new SocketClient($loginId, $message['text']));

        TelegramHistory::query()
            ->create([
                'initiator' => 'telegram',
                'login_id' => $loginId,
                'message' => $message,
            ]);

        return response()->noContent();
    }
}

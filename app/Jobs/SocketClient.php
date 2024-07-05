<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SocketClient implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public string $loginId,
        public string $message,
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new Channel("extension-user.{$this->loginId}");
    }

    public function broadcastAs()
    {
        return 'incoming';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
        ];
    }
}

<?php

namespace App\Events;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomReleased
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Room $room,
        public User $releasedBy
    ) {}
}

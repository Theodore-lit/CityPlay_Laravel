<?php

namespace App\Notifications;

use App\Models\Notification;
use App\Models\User;
use App\Models\Location;
use App\Models\City;

class MissionInvitation
{
    protected $inviter;
    protected $lobbySessionId;
    protected $location;
    protected $city;

    public function __construct(User $inviter, $lobbySessionId, Location $location, City $city)
    {
        $this->inviter = $inviter;
        $this->lobbySessionId = $lobbySessionId;
        $this->location = $location;
        $this->city = $city;
    }

    public function send(User $notifiable)
    {
        Notification::create([
            'user_id' => $notifiable->id,
            'type' => 'mission_invitation',
            'message' => "{$this->inviter->name} vous invite à une mission à {$this->city->name}",
            'link' => route('player.mission-lobby', $this->lobbySessionId),
        ]);
    }
}

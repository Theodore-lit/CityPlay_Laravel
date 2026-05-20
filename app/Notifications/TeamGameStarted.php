<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Team;
use App\Models\City;
use App\Models\GameSession;

class TeamGameStarted extends Notification
{
    use Queueable;

    protected $team;
    protected $city;
    protected $session;
    protected $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct(Team $team, City $city, GameSession $session, $sender)
    {
        $this->team = $team;
        $this->city = $city;
        $this->session = $session;
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'team_game_started',
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'city_id' => $this->city->id,
            'city_name' => $this->city->name,
            'session_id' => $this->session->id,
            'sender_name' => $this->sender->name,
            'message' => "{$this->sender->name} a lancé une partie avec l'équipe {$this->team->name} à {$this->city->name} !",
            'action_url' => route('teams.join-game', ['team' => $this->team->id, 'city' => $this->city->id, 'session' => $this->session->id]),
        ];
    }
}

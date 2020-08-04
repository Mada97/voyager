<?php

namespace App\Notifications;
use App\User;
use App\Trip;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferAccepted extends Notification
{
    use Queueable;

    public $user, $trip;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Trip $trip)
    {
        $this->user = $user;
        $this->trip = $trip;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }


    public function toArray($notifiable)
    {
        return [
            'name' => $this->user->name,
            'message' => $this->user->name . ' Accepted your offer on his trip to ' . $this->trip->to . '.',
            'trip' => $this->trip->id,
            'avatar' => $this->user->avatar
        ];
    }
}

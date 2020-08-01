<?php

namespace App\Notifications;

use App\Trip;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOffer extends Notification
{
    use Queueable;

    public $user;
    public $trip;

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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'name' => $this->user->name,
            'message' => $this->user->name . ' made an offer on your trip.',
            'trip' => $this->trip->id
        ];
    }


}

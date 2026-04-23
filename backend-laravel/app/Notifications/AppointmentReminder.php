<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AppointmentReminder extends Notification
{
    protected $appointment;

    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    public function via($notifiable)
    {
        return ['mail']; // On peut ajouter 'database' plus tard pour un dashboard
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Rappel : Votre rendez-vous de demain')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Ceci est un rappel pour votre rendez-vous prévu demain au cabinet.')
            ->line('📅 **Date :** ' . \Carbon\Carbon::parse($this->appointment->appointment_date)->format('d/m/Y'))
            ->line('⏰ **Heure :** ' . \Carbon\Carbon::parse($this->appointment->appointment_date)->format('H:i'))
            ->action('Confirmer ma présence', url('/appointments/confirm/' . $this->appointment->id))
            ->line('Si vous avez un empêchement, merci de nous contacter au plus vite.')
            ->salutation('Cordialement, l\'équipe du Cabinet Médical.');
    }
}
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class AppointmentCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu cita médica ha sido registrada')
            ->greeting("Hola {$notifiable->nombre}")
            ->line('Tu cita médica fue registrada exitosamente.')
            ->line("📅 Fecha: {$this->appointment->fecha_hora_inicio}")
            ->line("🧑‍⚕️ Médico: {$this->appointment->doctor->nombre}")
            ->line('Gracias por confiar en nuestro sistema.');
    }
}
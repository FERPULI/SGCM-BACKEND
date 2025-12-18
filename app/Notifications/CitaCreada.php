<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Cita;

class CitaCreada extends Notification implements ShouldQueue
{
    use Queueable;

    public $cita;

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu cita médica ha sido registrada')
            ->greeting('Hola ' . $notifiable->nombre)
            ->line('Tu cita médica fue registrada correctamente.')
            ->line('📅 Fecha y hora: ' . $this->cita->fecha_hora_inicio)
            ->line('🧑‍⚕️ Médico: ' . $this->cita->medico->user->name)
            ->line('Gracias por utilizar nuestro sistema.');
    }
}
<x-mail::message>
# Cita Registrada

Hola **{{ $paciente }}**,

Hemos recibido tu solicitud de cita médica. Un miembro de nuestro equipo o el Dr./Dra. **{{ $medico }}** la revisará y te enviaremos una confirmación.

**Detalles de la Cita:**
* **Fecha:** {{ $cita->fecha_hora_inicio->format('d/m/Y') }}
* **Hora:** {{ $cita->fecha_hora_inicio->format('h:i A') }}
* **Motivo:** {{ $cita->motivo_consulta }}

Gracias por usar nuestros servicios.

<x-mail::button :url="url('/paciente/citas')">
Ver Mis Citas
</x-mail::button>

Saludos,
{{ config('app.name') }}
</x-mail::message>
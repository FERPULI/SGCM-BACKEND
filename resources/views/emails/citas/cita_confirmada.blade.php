<x-mail::message>
# ¡Cita Confirmada!

Hola **{{ $paciente }}**,

Nos complace informarte que tu cita con el Dr./Dra. **{{ $medico }}** ha sido **CONFIRMADA**.

Por favor, preséntate 10 minutos antes de la hora programada.

**Detalles de la Cita:**
* **Fecha:** {{ $cita->fecha_hora_inicio->format('d/m/Y') }}
* **Hora:** {{ $cita->fecha_hora_inicio->format('h:i A') }}

<x-mail::button :url="url('/paciente/citas')">
Ver Detalles
</x-mail::button>

Gracias,
{{ config('app.name') }}
</x-mail::message>
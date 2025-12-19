<?php

namespace Tests\Feature\Horario;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class HorarioForbiddenRoleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function usuario_no_medico_no_puede_crear_horario()
    {
        $user = User::factory()->create(['rol' => 'paciente']);

        $response = $this->actingAs($user)
            ->postJson('/api/horarios', []);

        // La ruta no existe para este rol
        $response->assertStatus(404);
    }
}

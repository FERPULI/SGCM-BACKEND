<?php

namespace Tests\Feature\Historial;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class HistorialForbiddenTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function usuario_no_paciente_no_puede_ver_historial()
    {
        $user = User::factory()->create(['rol' => 'admin']);

        $response = $this->actingAs($user)
            ->getJson('/api/historial');

        // La ruta no existe para este rol
        $response->assertStatus(404);
    }
}

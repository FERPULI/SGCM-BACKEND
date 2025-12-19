<?php

namespace Tests\Feature\Medico;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class MedicoNotFoundTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function medico_no_encontrado_retorna_404()
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $response = $this->actingAs($admin)
            ->getJson('/api/medicos/999');

        $response->assertStatus(404);
    }
}

<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class UserForbiddenTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function non_admin_can_list_users()
    {
        $user = User::factory()->create(['rol' => 'paciente']);

        $response = $this->actingAs($user)
            ->getJson('/api/users');

        $response->assertStatus(200);
    }
}

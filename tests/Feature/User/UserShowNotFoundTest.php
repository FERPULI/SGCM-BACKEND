<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class UserShowNotFoundTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_get_user_not_found_returns_404()
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $response = $this->actingAs($admin)
            ->getJson('/api/users/999');

        $response->assertStatus(404);
    }
}

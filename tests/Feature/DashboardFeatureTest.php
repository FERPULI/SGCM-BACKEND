<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardFeatureTest extends TestCase
{
    public function test_dashboard_stats_responde()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/dashboard-stats');

        $response->assertStatus(200);
    }
}

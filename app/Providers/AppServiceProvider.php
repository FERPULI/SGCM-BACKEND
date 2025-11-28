<?php

namespace App\Providers;

use App\Models\Cita; // <-- 1. IMPORTA EL MODELO
use App\Policies\CitaPolicy; // <-- 2. IMPORTA LA POLÍTICA
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// ...

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // ... (otros policies si los tienes)
        Cita::class => CitaPolicy::class, // <-- 3. AÑADE ESTA LÍNEA
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        //
    }
}
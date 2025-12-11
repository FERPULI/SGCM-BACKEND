<?php

namespace Tests\Unit;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Providers\AppServiceProvider;

class AppServiceProviderTest extends TestCase
{
    #[Test]
    public function puede_instanciarse_correctamente()
    {
        $provider = new AppServiceProvider(app());
        $this->assertInstanceOf(AppServiceProvider::class, $provider);
    }

    #[Test]
    public function metodo_register_se_ejecuta_sin_errores()
    {
        $provider = new AppServiceProvider(app());
        $this->assertNull($provider->register());
    }

    #[Test]
    public function metodo_boot_se_ejecuta_sin_errores()
    {
        $provider = new AppServiceProvider(app());
        $this->assertNull($provider->boot());
    }

    #[Test]
    public function extiende_de_service_provider()
    {
        $provider = new AppServiceProvider(app());
        $this->assertInstanceOf(
            \Illuminate\Support\ServiceProvider::class,
            $provider
        );
    }
}

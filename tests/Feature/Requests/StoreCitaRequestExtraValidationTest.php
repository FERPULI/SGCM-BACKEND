<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\StoreCitaRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreCitaRequestExtraValidationTest extends TestCase
{
    public function test_falla_si_fecha_fin_es_antes_de_inicio(): void
    {
        $request = new StoreCitaRequest();

        $validator = Validator::make([
            'fecha_hora_inicio' => now()->addDay(),
            'fecha_hora_fin' => now(),
        ], $request->rules());

        $this->assertTrue($validator->fails());
    }
}

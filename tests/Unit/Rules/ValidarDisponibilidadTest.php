<?php

use App\Rules\ValidarDisponibilidadMedico;

test('la regla retorna mensaje de error correctamente', function () {
    $rule = new ValidarDisponibilidadMedico(1, '2025-01-01', '10:00');

    $result = $rule->message();

    expect($result)->toBeString();
});


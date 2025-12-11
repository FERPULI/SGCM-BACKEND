# PRUEBAS UNITARIAS – BACKEND SGCM

## 1. Introducción

Este documento describe el trabajo realizado en la implementación de pruebas unitarias para el backend del Sistema de Gestión de Citas Médicas (SGCM). Las pruebas fueron desarrolladas utilizando **Pest PHP** y se ejecutan completamente en memoria, sin interacción con la base de datos. También incluye pruebas para Policies, Rules personalizadas y Providers esenciales del framework.

## 2. Objetivo

Garantizar el correcto funcionamiento del dominio del sistema mediante pruebas unitarias aisladas que validan:

- Integridad y comportamiento de los modelos Eloquent.  
- Relaciones entre entidades.  
- Reglas personalizadas utilizadas en validación.  
- Políticas para autorización.  
- Service Providers fundamentales.

## 3. Alcance

Incluye pruebas unitarias de:

- Modelos Eloquent.  
- Policy: `CitaPolicy`.  
- Rule: `ValidarDisponibilidadMedico`.  
- Provider: `AppServiceProvider`.

Excluye:

- Controladores.  
- Servicios o repositorios.  
- Endpoints API.  
- Interacción real con la base de datos.

## 4. Herramientas utilizadas

- Laravel Framework  
- Pest PHP  
- PHPUnit  
- Eloquent ORM  
- PHP 8+  
- Mockery para simular modelos y consultas encadenadas

## 5. Ubicación de las pruebas

Todas las pruebas se encuentran en:

SGCM-BACKEND/tests/Unit

markdown
Copiar código

## 6. Componentes Probados

### 6.1 Modelos Eloquent

Modelos validados:

- `User`  
- `Paciente`  
- `Medico`  
- `Especialidad`  
- `Cita`  
- `HistorialMedico`  
- `Notificacion`  
- `DisponibilidadMedico`  
- `BloqueoHorario`  

Validaciones realizadas:

- Atributos disponibles en memoria.  
- Relaciones Eloquent (`belongsTo`, `hasMany`, etc.).  
- Casts y tipos (ej. fechas tratadas como `Carbon`).  
- Comportamiento en memoria sin persistencia.

### 6.2 Policy: CitaPolicy

Validaciones:

- Permisos para `view`, `create`, `update`, `delete` según rol y propiedad (paciente/medico/admin).  
- Simulación de usuarios y citas en memoria (sin DB).  
- Casos positivos (propietario) y negativos (usuario distinto).

### 6.3 Rule: ValidarDisponibilidadMedico

Validaciones:

- Acepta horarios válidos dentro de la disponibilidad del médico.  
- Rechaza cuando existe cita en conflicto.  
- Rechaza cuando existe bloqueo de horario.  
- Rechaza cuando la hora está fuera del rango laboral.  
- Uso intensivo de Mockery para simular consultas Eloquent encadenadas (`where()`, `first()`, `exists()`).  
- Ejecución vía `validate()` (implementación `ValidationRule` / `DataAwareRule`).

### 6.4 Provider: AppServiceProvider

Validaciones:

- Instanciación correcta del provider.  
- Ejecución de `register()` y `boot()` sin errores (smoke tests).  
- Confirmación de herencia desde `Illuminate\Support\ServiceProvider`.

## 7. Estrategia de pruebas

- Aislamiento total de base de datos (tests puramente unitarios).  
- Creación de objetos con `make()` o instanciación directa.  
- Simulación mediante Mockery para consultas encadenadas.  
- Validación directa de Rules y Policies en memoria.  
- Smoke tests para Providers.

## 8. Ejecución de las pruebas

Ejecutar todas las pruebas:

Ejecutar todas las pruebas:

./vendor/bin/pest

Ejecutar una prueba específica:

./vendor/bin/pest --filter NombreDelTest

## 9. Resultados

Todas las pruebas unitarias implementadas (modelos, policies, rules y providers) se ejecutaron correctamente en el entorno de desarrollo.

No se detectaron comportamientos inesperados en la lógica del dominio.

ValidarDisponibilidadMedico demostró robustez ante múltiples escenarios simulados (cita existente, bloqueo, fuera del horario, disponible).

CitaPolicy confirmó el control de acceso esperado según rol (paciente, médico, admin) y pertenencia de recurso.

AppServiceProvider se cargó e inicializó sin excepciones.

## 10. Buenas Prácticas Aplicadas

Pruebas unitarias 100% aisladas y deterministas.

Uso de Mockery para simular dependencias y consultas Eloquent complejas.

Separación clara entre unitarios e integración.

Nombres descriptivos y escenarios bien documentados en los tests.

Documentación versionada dentro del repositorio.

## 11. Conclusión

La batería de pruebas implementada proporciona una cobertura sólida sobre el dominio central del backend (modelos, reglas de negocio, políticas de autorización y provider base). Esto reduce el riesgo de regresiones, facilita refactorizaciones y sirve como respaldo técnico para futuros desarrollos.

## 12. Archivos clave y rutas de referencia

## Policy

Código: app/Policies/CitaPolicy.php

Test: tests/Unit/CitaPolicyTest.php

## Rule

Código: app/Rules/ValidarDisponibilidadMedico.php

Test: tests/Unit/ValidarDisponibilidadMedicoTest.php

## Provider

Código: app/Providers/AppServiceProvider.php

Test: tests/Unit/AppServiceProviderTest.php

## Model Tests (ejemplos)

tests/Unit/MedicoModelTest.php

tests/Unit/PacienteModelTest.php

tests/Unit/CitaModelTest.php

y demás *ModelTest.php según modelos del proyecto.

## 13. Próximos pasos recomendados

Añadir pruebas de integración (Feature tests) con SQLite in-memory para endpoints críticos.

Incluir controladores y pruebas end-to-end en el plan de test.

Configurar pipeline CI (GitHub Actions / GitLab CI) que ejecute tests y genere reportes de cobertura (HTML/XML).

Aumentar cobertura hacia servicios y lógica de negocio fuera de modelos.

Revisar y mantener las factories y seeders de prueba para casos de integración.


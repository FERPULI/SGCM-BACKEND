# Documentación de Pruebas Unitarias – Backend SGCM

## 1. Introducción

Este documento describe el trabajo realizado en la implementación de pruebas unitarias
para el Backend del Sistema de Gestión de Citas Médicas (SGCM).

Las pruebas fueron desarrolladas utilizando Pest PHP y se ejecutan completamente
en memoria, sin interacción con la base de datos.

---

## 2. Objetivo

Garantizar el correcto funcionamiento de los modelos del sistema mediante pruebas unitarias
aisladas que validan:
- Atributos de los modelos
- Definición de relaciones Eloquent
- Tipos de datos
- Integridad del dominio

---

## 3. Alcance

Las pruebas cubren exclusivamente modelos Eloquent.
No se incluyen pruebas de:
- Controladores
- Servicios
- Base de datos
- APIs

---

## 4. Herramientas Utilizadas

- Laravel Framework
- Pest PHP
- PHPUnit
- Eloquent ORM
- PHP 8+

---

## 5. Ubicación de las Pruebas

Las pruebas unitarias se encuentran en el proyecto backend en la ruta:

SGCM-BACKEND/tests/Unit

---

## 6. Modelos Probados

Los siguientes modelos fueron validados mediante pruebas unitarias:

- User
- Paciente
- Medico
- Especialidad
- Cita
- HistorialMedico
- Notificacion
- DisponibilidadMedico
- BloqueoHorario

Cada modelo cuenta con su archivo de prueba independiente.

---

## 7. Estrategia de Pruebas

### Pruebas en memoria
Los modelos se crean sin persistencia usando make() o asignación directa.
No se utiliza save() ni create().

### Relaciones
Las relaciones se validan comprobando la definición correcta:
- belongsTo
- hasMany

No se consultan registros reales.

### Tipos de datos
Se valida que los identificadores sean tratados como enteros en memoria.

---

## 8. Ejecución de las Pruebas

Ejecutar todas las pruebas:

./vendor/bin/pest

Ejecutar una prueba específica:

./vendor/bin/pest --filter NombreDelTest

---

## 9. Resultados

Todas las pruebas unitarias se ejecutaron correctamente sin errores.
El dominio del sistema se encuentra validado y estable.

---

## 10. Buenas Prácticas Aplicadas

- Pruebas aisladas
- Sin dependencia de base de datos
- Código de test claro y mantenible
- Documentación versionada junto al proyecto

---

## 11. Conclusión

La implementación de estas pruebas unitarias asegura la calidad, estabilidad
y mantenibilidad del backend del sistema SGCM, sirviendo como respaldo técnico
y académico del proyecto.

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carrera;
use App\Models\Periodo;
use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\Inscripcion;
use App\Models\Calificacion;
use App\Models\Asistencia;

class DesercionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('es_ES');
        $faker->seed(12345);

        // 1. Crear Carreras
        $carreras = [
            Carrera::create(['nombre' => 'Ciencias de la Informática', 'clave' => 'LCI']),
            Carrera::create(['nombre' => 'Ingeniería Industrial', 'clave' => 'II']),
            Carrera::create(['nombre' => 'Administración Industrial', 'clave' => 'AI']),
        ];

        // 2. Crear 4 Periodos Semestrales
        $periodos = [
            Periodo::create(['codigo' => '2025-1', 'descripcion' => 'Primer Semestre 2025']),
            Periodo::create(['codigo' => '2025-2', 'descripcion' => 'Segundo Semestre 2025']),
            Periodo::create(['codigo' => '2026-1', 'descripcion' => 'Primer Semestre 2026']),
            Periodo::create(['codigo' => '2026-2', 'descripcion' => 'Segundo Semestre 2026']),
        ];

        // 3. Crear Materias por carrera (4 semestres)
        $materiasPorCarrera = [];
        foreach ($carreras as $carrera) {
            for ($sem = 1; $sem <= 4; $sem++) {
                $materiasPorCarrera[$carrera->id][$sem] = [
                    Materia::create(['carrera_id' => $carrera->id, 'nombre' => "Matemáticas $sem ({$carrera->clave})", 'semestre_plan' => $sem]),
                    Materia::create(['carrera_id' => $carrera->id, 'nombre' => "Fundamentos Técnicos $sem ({$carrera->clave})", 'semestre_plan' => $sem]),
                    Materia::create(['carrera_id' => $carrera->id, 'nombre' => "Gestión y Proyectos $sem ({$carrera->clave})", 'semestre_plan' => $sem]),
                ];
            }
        }

        // 4. Generar 300 Estudiantes con perfiles de comportamiento
        for ($i = 1; $i <= 300; $i++) {
            $carrera = $carreras[array_rand($carreras)];
            
            // Asignar perfil: 60% Regular, 25% Riesgo por Faltas/Trabajo, 15% Rezago Académico
            $randPerfil = rand(1, 100);
            if ($randPerfil <= 60) {
                $perfil = 'regular';
                $deserta = false;
                $semestreAbandono = null;
            } elseif ($randPerfil <= 85) {
                $perfil = 'riesgo_asistencia';
                $deserta = rand(1, 100) <= 80; // 80% de probabilidad de desertar
                $semestreAbandono = rand(1, 3);
            } else {
                $perfil = 'rezago_academico';
                $deserta = rand(1, 100) <= 75;
                $semestreAbandono = rand(2, 3);
            }

            $trabaja = ($perfil === 'riesgo_asistencia') ? true : (rand(1, 100) <= 25);
            $tieneBeca = ($perfil === 'regular') ? (rand(1, 100) <= 60) : (rand(1, 100) <= 15);

            $estudiante = Estudiante::create([
                'matricula' => '2025' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'carrera_id' => $carrera->id,
                'edad_ingreso' => rand(18, 24),
                'tiene_beca' => $tieneBeca,
                'trabaja' => $trabaja,
                'estado_final' => $deserta ? 'abandono' : 'activo',
            ]);

            $maxSemestres = $deserta ? $semestreAbandono : 4;

            for ($sem = 1; $sem <= $maxSemestres; $sem++) {
                $esUltimoYDeserta = ($deserta && $sem === $maxSemestres);

                $inscripcion = Inscripcion::create([
                    'estudiante_id' => $estudiante->id,
                    'periodo_id' => $periodos[$sem - 1]->id,
                    'semestre_cursado' => $sem,
                    'estatus' => $esUltimoYDeserta ? 'desercion' : 'inscrito',
                ]);

                foreach ($materiasPorCarrera[$carrera->id][$sem] as $materia) {
                    // Generar calificaciones según perfil
                    if ($perfil === 'regular') {
                        $p1 = rand(75, 98) / 10;
                        $p2 = rand(78, 100) / 10;
                    } elseif ($perfil === 'riesgo_asistencia') {
                        $p1 = rand(65, 82) / 10;
                        $p2 = $esUltimoYDeserta ? rand(40, 62) / 10 : rand(55, 75) / 10;
                    } else {
                        $p1 = rand(45, 65) / 10;
                        $p2 = rand(40, 60) / 10;
                    }

                    $final = round(($p1 + $p2) / 2, 2);

                    Calificacion::create([
                        'inscripcion_id' => $inscripcion->id,
                        'materia_id' => $materia->id,
                        'parcial_1' => $p1,
                        'parcial_2' => $p2,
                        'calificacion_final' => $final,
                        'reprobada' => $final < 6.0,
                    ]);

                    // Generar asistencia mensual (4 meses por semestre)
                    for ($mes = 1; $mes <= 4; $mes++) {
                        $clasesTotales = 16;
                        if ($perfil === 'regular') {
                            $asistidas = rand(14, 16);
                        } elseif ($perfil === 'riesgo_asistencia') {
                            // Va cayendo conforme avanza el semestre
                            $asistidas = max(6, rand(13 - $mes, 15 - $mes));
                        } else {
                            $asistidas = rand(11, 14);
                        }

                        Asistencia::create([
                            'inscripcion_id' => $inscripcion->id,
                            'materia_id' => $materia->id,
                            'mes' => $mes,
                            'clases_totales' => $clasesTotales,
                            'clases_asistidas' => $asistidas,
                            'porcentaje_asistencia' => round(($asistidas / $clasesTotales) * 100, 2),
                        ]);
                    }
                }
            }
        }
    }
}
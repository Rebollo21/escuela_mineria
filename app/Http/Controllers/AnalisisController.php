<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AnalisisController extends Controller
{
    public function index()
    {
        // 1. Consulta SQL Consolidada por Estudiante
        $estudiantes = DB::select("
            SELECT 
                e.id,
                e.matricula,
                c.nombre AS carrera,
                c.clave AS carrera_clave,
                e.edad_ingreso,
                e.tiene_beca,
                e.trabaja,
                e.estado_final,
                COUNT(DISTINCT i.id) AS semestres_cursados,
                ROUND(AVG(cal.calificacion_final), 2) AS promedio_general,
                SUM(CASE WHEN cal.reprobada = 1 THEN 1 ELSE 0 END) AS materias_reprobadas,
                ROUND(AVG(asis.porcentaje_asistencia), 2) AS asistencia_promedio
            FROM estudiantes e
            INNER JOIN carreras c ON e.carrera_id = c.id
            LEFT JOIN inscripciones i ON e.id = i.estudiante_id
            LEFT JOIN calificaciones cal ON i.id = cal.inscripcion_id
            LEFT JOIN asistencias asis ON i.id = asis.inscripcion_id
            GROUP BY e.id, e.matricula, c.nombre, c.clave, e.edad_ingreso, e.tiene_beca, e.trabaja, e.estado_final
        ");

        // 2. Modelo de Clasificación y Métricas (Train/Test 80/20)
        $tp = 0; $tn = 0; $fp = 0; $fn = 0;
        $totalAlumnos = count($estudiantes);$indiceCorte = (int) ($totalAlumnos * 0.8);$datasetClustering = [];

        foreach ($estudiantes as$idx => $est) {$score = 0;
            if ($est->promedio_general < 6.5)$score += 35;
            elseif ($est->promedio_general < 7.5)$score += 15;

            if ($est->asistencia_promedio < 75)$score += 35;
            elseif ($est->asistencia_promedio < 85)$score += 15;

            $score += min(20,$est->materias_reprobadas * 5);
            if ($est->trabaja && !$est->tiene_beca)$score += 10;

            $est->score_riesgo = min(100, $score);$est->nivel_riesgo = $score >= 55 ? 'Alto' : ($score >= 30 ? 'Medio' : 'Bajo');
            $prediccionAbandono =$score >= 50 ? 'abandono' : 'activo';

            if ($idx >=$indiceCorte) {
                if ($prediccionAbandono === 'abandono' && $est->estado_final === 'abandono')$tp++;
                elseif ($prediccionAbandono === 'activo' && $est->estado_final === 'activo')$tn++;
                elseif ($prediccionAbandono === 'abandono' && $est->estado_final === 'activo')$fp++;
                elseif ($prediccionAbandono === 'activo' && $est->estado_final === 'abandono')$fn++;
            }

            $datasetClustering[] = [
                'id' => $est->id,
                'x' => (float) $est->promedio_general,
                'y' => (float) $est->asistencia_promedio,
            ];
        }

        $totalTest = max(1, $tp +$tn + $fp +$fn);
        $accuracy = round((($tp + $tn) /$totalTest) * 100, 1);
        $precision = ($tp + $fp) > 0 ? round(($tp / ($tp +$fp)) * 100, 1) : 0;
        $recall = ($tp + $fn) > 0 ? round(($tp / ($tp +$fn)) * 100, 1) : 0;
        $f1 = ($precision +$recall) > 0 ? round((2 * $precision * $recall) / ($precision +$recall), 1) : 0;

        $metricasModelo = [
            'total_estudiantes' => $totalAlumnos,
            'total_abandono' => count(array_filter($estudiantes, fn($e) =>$e->estado_final === 'abandono')),
            'en_riesgo_alto' => count(array_filter($estudiantes, fn($e) =>$e->nivel_riesgo === 'Alto')),
            'accuracy' => $accuracy,
            'precision' => $precision,
            'recall' => $recall,
            'f1_score' => $f1,
        ];

        // 3. Algoritmo K-Means
        $clusters = $this->ejecutarKMeans($datasetClustering, 3);
        foreach ($estudiantes as$idx => $est) {$est->cluster = $clusters[$idx];
        }

        // 4. Datos Comparativos para Gráficas Adicionales
        // A. Comparativa: Trabaja vs No Trabaja en Deserción
        $comparativaTrabajo = DB::select("
            SELECT 
                trabaja,
                estado_final,
                COUNT(*) AS total
            FROM estudiantes
            GROUP BY trabaja, estado_final
        ");

        // B. Comparativa General: Promedio y Asistencia de Activos vs Abandono
        $comparativaEstado = DB::select("
            SELECT 
                estado_final,
                ROUND(AVG(cal.calificacion_final), 2) AS promedio,
                ROUND(AVG(asis.porcentaje_asistencia), 2) AS asistencia
            FROM estudiantes e
            LEFT JOIN inscripciones i ON e.id = i.estudiante_id
            LEFT JOIN calificaciones cal ON i.id = cal.inscripcion_id
            LEFT JOIN asistencias asis ON i.id = asis.inscripcion_id
            GROUP BY estado_final
        ");

        // C. Impacto de Becas
        $impactoBecas = DB::select("
            SELECT tiene_beca, estado_final, COUNT(*) AS total
            FROM estudiantes GROUP BY tiene_beca, estado_final
        ");

        // D. Multi-Semestre y Mensual
        $comparativaSemestres = DB::select("
            SELECT 
                i.semestre_cursado AS semestre,
                ROUND(AVG(cal.calificacion_final), 2) AS promedio,
                ROUND(AVG(asis.porcentaje_asistencia), 2) AS asistencia
            FROM inscripciones i
            LEFT JOIN calificaciones cal ON i.id = cal.inscripcion_id
            LEFT JOIN asistencias asis ON i.id = asis.inscripcion_id
            GROUP BY i.semestre_cursado ORDER BY i.semestre_cursado ASC
        ");

        $asistenciaMensual = DB::select("
            SELECT 
                asis.mes,
                ROUND(AVG(CASE WHEN e.estado_final = 'activo' THEN asis.porcentaje_asistencia END), 2) AS activos,
                ROUND(AVG(CASE WHEN e.estado_final = 'abandono' THEN asis.porcentaje_asistencia END), 2) AS abandono
            FROM asistencias asis
            INNER JOIN inscripciones i ON asis.inscripcion_id = i.id
            INNER JOIN estudiantes e ON i.estudiante_id = e.id
            GROUP BY asis.mes ORDER BY asis.mes ASC
        ");

        $desercionCarreras = DB::select("
            SELECT c.clave, c.nombre, COUNT(e.id) AS total,
            SUM(CASE WHEN e.estado_final = 'abandono' THEN 1 ELSE 0 END) AS abandonos
            FROM carreras c LEFT JOIN estudiantes e ON c.id = e.carrera_id GROUP BY c.id, c.clave, c.nombre
        ");

        return view('dashboard', compact(
            'estudiantes', 'metricasModelo', 'comparativaSemestres', 
            'asistenciaMensual', 'desercionCarreras', 'impactoBecas', 
            'comparativaTrabajo', 'comparativaEstado'
        ));
    }

    private function ejecutarKMeans(array $puntos, int $k = 3, int$iteraciones = 15): array
    {
        $centroides = [['x' => 9.0, 'y' => 95.0], ['x' => 7.2, 'y' => 80.0], ['x' => 5.5, 'y' => 65.0]];$asignaciones = [];
        for ($iter = 0; $iter < $iteraciones; $iter++) {
            $grupos = array_fill(0,$k, []);
            foreach ($puntos as $idx =>$p) {
                $menorDist = PHP_FLOAT_MAX; $clusterElegido = 0;
                for ($c = 0; $c < $k; $c++) {
                    $dx = ($p['x'] - $centroides[$c]['x']) * 10;
                    $dy = $p['y'] -$centroides[$c]['y'];$dist = sqrt($dx * $dx + $dy * $dy);
                    if ($dist <$menorDist) { $menorDist =$dist; $clusterElegido =$c; }
                }
                $asignaciones[$idx] =$clusterElegido;
                $grupos[$clusterElegido][] =$p;
            }
            for ($c = 0; $c < $k; $c++) {
                if (count($grupos[$c]) > 0) {
                    $centroides[$c]['x'] = array_sum(array_column($grupos[$c], 'x')) / count($grupos[$c]);
                    $centroides[$c]['y'] = array_sum(array_column($grupos[$c], 'y')) / count($grupos[$c]);
                }
            }
        }
        return array_map(fn($c) => ['Perfil Óptimo', 'Perfil Intermedio', 'Perfil Crítico'][$c],$asignaciones);
    }
}
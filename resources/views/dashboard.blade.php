<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minería de Datos - Deserción Escolar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Encabezado -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Predicción de Deserción Escolar Universitaria</h1>
                <p class="text-slate-600 mt-1">Análisis cruzado completo: Activos vs Abandono, Impacto Laboral, Becas y Rendimiento</p>
            </div>

        </div>

        <!-- Tarjetas KPI -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <p class="text-sm font-medium text-slate-500">Estudiantes Analizados</p>
                <p class="text-3xl font-bold text-slate-900 mt-2">{{ $metricasModelo['total_estudiantes'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <p class="text-sm font-medium text-slate-500">Abandonos Históricos</p>
                <p class="text-3xl font-bold text-rose-600 mt-2">{{ $metricasModelo['total_abandono'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <p class="text-sm font-medium text-slate-500">Detectados en Riesgo Alto</p>
                <p class="text-3xl font-bold text-amber-600 mt-2">{{ $metricasModelo['en_riesgo_alto'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <p class="text-sm font-medium text-slate-500">Exactitud</p>
                <p class="text-3xl font-bold text-emerald-600 mt-2">{{ $metricasModelo['accuracy'] }}%</p>
            </div>
        </div>

        <!-- Fila 1: Activos vs Abandono (Promedio y Asistencia) & Trabaja vs No Trabaja -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 mb-4">1. Comparativa Global: Activos vs Abandono</h2>
                    <canvas id="chartEstadoGlobal" height="140"></canvas>
                </div>
                <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-600">
                    <span class="font-semibold text-slate-700">Interpretación:</span> Contrasta el promedio de calificaciones y la asistencia global entre los alumnos que concluyen sus estudios y los que desertan, demostrando la brecha directa de rendimiento.
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 mb-4">2. Comparativa Laboral: ¿Trabaja vs No Trabaja?</h2>
                    <canvas id="chartTrabajo" height="140"></canvas>
                </div>
                <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-600">
                    <span class="font-semibold text-slate-700">Interpretación:</span> Evalúa cuántos estudiantes con empleo activo logran mantenerse en la escuela frente a los que sufren deserción debido a la carga laboral externa.
                </div>
            </div>
        </div>

        <!-- Fila 2: Asistencia Mensual & Becados vs No Becados -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 mb-4">3. Evolución Mensual de Asistencia (Activos vs Abandono)</h2>
                    <canvas id="chartAsistencia" height="140"></canvas>
                </div>
                <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-600">
                    <span class="font-semibold text-slate-700">Interpretación:</span> Muestra cómo decae progresivamente la asistencia mes a mes en el grupo de deserción, sirviendo como el indicador temprano más fuerte de abandono.
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 mb-4">4. Impacto Socioeconómico: Con Beca vs Sin Beca</h2>
                    <canvas id="chartBecas" height="140"></canvas>
                </div>
                <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-600">
                    <span class="font-semibold text-slate-700">Interpretación:</span> Mide la efectividad de los apoyos económicos contrastando la tasa de retención y abandono entre estudiantes becados y no becados.
                </div>
            </div>
        </div>

        <!-- Fila 3: Clustering K-Means & Deserción por Carrera -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 mb-4">5. Clustering K-Means (Promedio vs Asistencia)</h2>
                    <canvas id="chartClusters" height="140"></canvas>
                </div>
                <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-600">
                    <span class="font-semibold text-slate-700">Interpretación:</span> Segmenta automáticamente a la población estudiantil en tres perfiles analíticos (Óptimo, Intermedio y Crítico) basándose en su rendimiento y asistencia.
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 mb-4">6. Deserción Histórica por Carrera</h2>
                    <canvas id="chartCarreras" height="140"></canvas>
                </div>
                <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-600">
                    <span class="font-semibold text-slate-700">Interpretación:</span> Compara el volumen total de alumnos activos frente a las bajas registradas en cada programa académico, identificando las carreras con mayor vulnerabilidad.
                </div>
            </div>
        </div>

        <!-- Tabla Interactiva con Filtros -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-12">
            <div class="px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="text-lg font-bold text-slate-800">Directorio, Comparativas y Filtros de Estudiantes</h2>
                
                <div class="flex flex-wrap gap-3">
                    <select id="filtroCarrera" class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todas las Carreras</option>
                    </select>
                    <select id="filtroRiesgo" class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todos los Riesgos</option>
                        <option value="Alto">Riesgo Alto</option>
                        <option value="Medio">Riesgo Medio</option>
                        <option value="Bajo">Riesgo Bajo</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="tablaEstudiantes" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-xs uppercase border-b border-slate-200">
                            <th class="py-3 px-4">Matrícula</th>
                            <th class="py-3 px-4">Carrera</th>
                            <th class="py-3 px-4">Promedio</th>
                            <th class="py-3 px-4">Asistencia</th>
                            <th class="py-3 px-4">¿Trabaja?</th>
                            <th class="py-3 px-4">¿Beca?</th>
                            <th class="py-3 px-4">Cluster K-Means</th>
                            <th class="py-3 px-4">Riesgo Estimado</th>
                            <th class="py-3 px-4">Estado Real</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-sm">
                        @foreach($estudiantes as $est)
                        <tr class="hover:bg-slate-50 fila-estudiante" data-carrera="{{ $est->carrera_clave }}" data-riesgo="{{ $est->nivel_riesgo }}">
                            <td class="py-3 px-4 font-medium">{{ $est->matricula }}</td>
                            <td class="py-3 px-4">{{ $est->carrera_clave }}</td>
                            <td class="py-3 px-4">{{ $est->promedio_general }}</td>
                            <td class="py-3 px-4">{{ $est->asistencia_promedio }}%</td>
                            <td class="py-3 px-4">{{ $est->trabaja ? 'Sí' : 'No' }}</td>
                            <td class="py-3 px-4">{{ $est->tiene_beca ? 'Sí' : 'No' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $est->cluster === 'Perfil Óptimo' ? 'bg-emerald-100 text-emerald-700' : ($est->cluster === 'Perfil Intermedio' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700') }}">
                                    {{ $est->cluster }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold
                                {{ $est->nivel_riesgo === 'Alto' ? 'text-rose-600' : ($est->nivel_riesgo === 'Medio' ? 'text-amber-600' : 'text-emerald-600') }}">
                                {{ $est->nivel_riesgo }} ({{ $est->score_riesgo }}%)
                            </td>
                            <td class="py-3 px-4 capitalize font-bold {{ $est->estado_final === 'abandono' ? 'text-rose-600' : 'text-emerald-600' }}">{{ $est->estado_final }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const estadoGlobal = @json($comparativaEstado);
        const trabajoData = @json($comparativaTrabajo);
        const asistenciaMes = @json($asistenciaMensual);
        const impactoBecas = @json($impactoBecas);
        const estudiantes = @json($estudiantes);
        const carreras = @json($desercionCarreras);

        // 1. Gráfica Estado Global (Activos vs Abandono)
        new Chart(document.getElementById('chartEstadoGlobal'), {
            type: 'bar',
            data: {
                labels: ['Estudiantes Activos', 'Estudiantes en Abandono'],
                datasets: [
                    { label: 'Promedio General', data: [estadoGlobal.find(e => e.estado_final == 'activo')?.promedio || 0, estadoGlobal.find(e => e.estado_final == 'abandono')?.promedio || 0], backgroundColor: 'rgba(16, 185, 129, 0.8)' },
                    { label: 'Asistencia Promedio (%)', data: [estadoGlobal.find(e => e.estado_final == 'activo')?.asistencia || 0, estadoGlobal.find(e => e.estado_final == 'abandono')?.asistencia || 0], backgroundColor: 'rgba(59, 130, 246, 0.8)' }
                ]
            }
        });

        // 2. Gráfica Comparativa Laboral (Trabaja vs No Trabaja)
        const getTrabajoVal = (trabaja, estado) => trabajoData.find(t => t.trabaja == trabaja && t.estado_final == estado)?.total || 0;
        new Chart(document.getElementById('chartTrabajo'), {
            type: 'bar',
            data: {
                labels: ['Trabaja', 'No Trabaja'],
                datasets: [
                    { label: 'Activos', data: [getTrabajoVal(1, 'activo'), getTrabajoVal(0, 'activo')], backgroundColor: 'rgba(99, 102, 241, 0.8)' },
                    { label: 'Abandono', data: [getTrabajoVal(1, 'abandono'), getTrabajoVal(0, 'abandono')], backgroundColor: 'rgba(244, 63, 94, 0.8)' }
                ]
            },
            options: { scales: { x: { stacked: true }, y: { stacked: true } } }
        });

        // 3. Gráfica Asistencia Mensual
        new Chart(document.getElementById('chartAsistencia'), {
            type: 'line',
            data: {
                labels: asistenciaMes.map(m => 'Mes ' + m.mes),
                datasets: [
                    { label: 'Activos (%)', data: asistenciaMes.map(m => m.activos), borderColor: 'rgb(16, 185, 129)', backgroundColor: 'rgba(16, 185, 129, 0.2)', fill: true, tension: 0.3 },
                    { label: 'Abandono (%)', data: asistenciaMes.map(m => m.abandono), borderColor: 'rgb(225, 29, 72)', backgroundColor: 'rgba(225, 29, 72, 0.2)', fill: true, tension: 0.3 }
                ]
            }
        });

        // 4. Gráfica Becas
        const getBecaVal = (beca, estado) => impactoBecas.find(b => b.tiene_beca == beca && b.estado_final == estado)?.total || 0;
        new Chart(document.getElementById('chartBecas'), {
            type: 'bar',
            data: {
                labels: ['Con Beca', 'Sin Beca'],
                datasets: [
                    { label: 'Activos', data: [getBecaVal(1, 'activo'), getBecaVal(0, 'activo')], backgroundColor: 'rgba(16, 185, 129, 0.8)' },
                    { label: 'Abandono', data: [getBecaVal(1, 'abandono'), getBecaVal(0, 'abandono')], backgroundColor: 'rgba(239, 68, 68, 0.8)' }
                ]
            },
            options: { scales: { x: { stacked: true }, y: { stacked: true } } }
        });

        // 5. Gráfica Clusters K-Means
        const agruparCluster = (nombre) => estudiantes.filter(e => e.cluster === nombre).map(e => ({ x: parseFloat(e.promedio_general), y: parseFloat(e.asistencia_promedio) }));
        new Chart(document.getElementById('chartClusters'), {
            type: 'scatter',
            data: {
                datasets: [
                    { label: 'Perfil Óptimo', data: agruparCluster('Perfil Óptimo'), backgroundColor: 'rgb(16, 185, 129)' },
                    { label: 'Perfil Intermedio', data: agruparCluster('Perfil Intermedio'), backgroundColor: 'rgb(59, 130, 246)' },
                    { label: 'Perfil Crítico', data: agruparCluster('Perfil Crítico'), backgroundColor: 'rgb(225, 29, 72)' }
                ]
            },
            options: { scales: { x: { title: { display: true, text: 'Promedio General' } }, y: { title: { display: true, text: 'Asistencia (%)' } } } }
        });

        // 6. Gráfica Deserción por Carrera
        new Chart(document.getElementById('chartCarreras'), {
            type: 'bar',
            data: {
                labels: carreras.map(c => c.clave),
                datasets: [
                    { label: 'Activos', data: carreras.map(c => c.total - c.abandonos), backgroundColor: 'rgba(99, 102, 241, 0.8)' },
                    { label: 'Abandonos', data: carreras.map(c => c.abandonos), backgroundColor: 'rgba(244, 63, 94, 0.8)' }
                ]
            },
            options: { scales: { x: { stacked: true }, y: { stacked: true } } }
        });

        // --- Filtros de la Tabla ---
        const filtroCarrera = document.getElementById('filtroCarrera');
        const filtroRiesgo = document.getElementById('filtroRiesgo');
        const filas = document.querySelectorAll('.fila-estudiante');

        [...new Set(estudiantes.map(e => e.carrera_clave))].forEach(clave => {
            const opt = document.createElement('option');
            opt.value = clave; opt.textContent = clave;
            filtroCarrera.appendChild(opt);
        });

        function filtrarTabla() {
            const cVal = filtroCarrera.value, rVal = filtroRiesgo.value;
            filas.forEach(fila => {
                const matchC = !cVal || fila.getAttribute('data-carrera') === cVal;
                const matchR = !rVal || fila.getAttribute('data-riesgo') === rVal;
                fila.style.display = (matchC && matchR) ? '' : 'none';
            });
        }
        filtroCarrera.addEventListener('change', filtrarTabla);
        filtroRiesgo.addEventListener('change', filtrarTabla);
    </script>
</body>
</html>
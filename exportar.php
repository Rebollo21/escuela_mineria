<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$tablas = ['carreras', 'periodos', 'estudiantes', 'materias', 'inscripciones', 'calificaciones', 'asistencias'];
$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0);

foreach ($tablas as $index => $tabla) {
    $datos = DB::table($tabla)->get();
    $sheet = $spreadsheet->createSheet($index);
    $sheet->setTitle(substr($tabla, 0, 31));
    
    if ($datos->isEmpty()) continue;
    
    $headers = array_keys((array)$datos->first());
    $sheet->fromArray($headers, NULL, 'A1');
    
    $filaArray = [];
    foreach ($datos as $fila) {
        $filaArray[] = array_values((array)$fila);
    }
    $sheet->fromArray($filaArray, NULL, 'A2');
    
    // Estilo corporativo para la cabecera
    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);
    
    $sheet->freezePane('A2');
}

$writer = new Xlsx($spreadsheet);
$writer->save('desercion_escolar_completo.xlsx');
echo "¡Archivo Excel generado exitosamente en la raíz del proyecto!\n";
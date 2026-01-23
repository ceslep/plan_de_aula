<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;

const SPREADSHEET_ID = '1QrTeZH7VhvRFfWvr2OKti80ePAO2qMN2DDLI6Lcm5Kc';
const RANGE = 'Datos!A:G'; // Ajusta columnas si cambias el orden


$allowed_origins = [
    'https://app.iedeoccidente.com',
    'http://localhost:5173',
    'https://ceslep.github.io'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Función para inicializar cliente
function getClient() {
    $client = new Client();
    $client->setApplicationName('Plan Mejoramiento');
    $client->setScopes([Sheets::SPREADSHEETS]);
    $client->setAuthConfig($serviceAccountPath = __DIR__ . '/assets/serviceaccount.json');
    $client->setAccessType('offline');
    return $client;
}

// 🚀 Recibir datos del formulario
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["error" => "No se recibieron datos"]);
    exit;
}

try {
    $client = getClient();
    $service = new Sheets($client);

    date_default_timezone_set('America/Bogota');
    $fechaHora = date('Y-m-d H:i:s'); // Fecha/hora registro

    $grupo      = $data['grupo'] ?? '';
    $asignatura = $data['asignatura'] ?? '';
    $docente    = $data['docenteSeleccionado'] ?? '';
    $fechaLimite= $data['fechaLimite'] ?? '';
    $plan       = $data['planMejoramiento'] ?? '';
    $estudiantes= $data['nombresEstudiante'] ?? [];

    // 1️⃣ Leer todos los valores existentes en la hoja
    $response = $service->spreadsheets_values->get(SPREADSHEET_ID, RANGE);
    $rows = $response->getValues() ?? [];

    $valuesToUpdate = [];

    foreach ($estudiantes as $estudiante) {
        $found = false;
        $rowIndex = 0;

        // 2️⃣ Buscar coincidencia en las filas
        foreach ($rows as $index => $row) {
            $rowGrupo      = $row[0] ?? '';
            $rowAsignatura = $row[1] ?? '';
            $rowDocente    = $row[2] ?? '';
            $rowEstudiante = $row[3] ?? '';

            if ($rowGrupo === $grupo && 
                $rowAsignatura === $asignatura && 
                $rowDocente === $docente &&
                $rowEstudiante === $estudiante) {
                $found = true;
                $rowIndex = $index + 1; // +1 porque Google Sheets es 1-based
                break;
            }
        }

        if ($found) {
            // 3️⃣ Si existe → Actualizar plan y fecha límite
            $updateRange = "Datos!E{$rowIndex}:G{$rowIndex}";
            $updateBody = new Google\Service\Sheets\ValueRange([
                'values' => [[ $plan, $fechaLimite, $fechaHora ]]
            ]);
            $service->spreadsheets_values->update(
                SPREADSHEET_ID,
                $updateRange,
                $updateBody,
                ['valueInputOption' => 'RAW']
            );
        } else {
            // 4️⃣ Si no existe → Insertar nueva fila
            $valuesToUpdate[] = [
                $grupo,
                $asignatura,
                $docente,
                $estudiante,
                $plan,
                $fechaLimite,
                $fechaHora
            ];
        }
    }

    if (!empty($valuesToUpdate)) {
        $body = new Google\Service\Sheets\ValueRange(['values' => $valuesToUpdate]);
        $params = ['valueInputOption' => 'RAW'];
        $service->spreadsheets_values->append(SPREADSHEET_ID, RANGE, $body, $params);
    }

    echo json_encode(["success" => true, "message" => "Datos procesados correctamente"]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

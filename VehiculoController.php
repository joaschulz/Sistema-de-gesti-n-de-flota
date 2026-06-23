<?php
require_once 'IntervencionDAO.php';

// 1. CONFIGURACIÓN DE FRONTERA (Garantiza que siempre se responda en formato JSON)
header('Content-Type: application/json; charset=utf-8');

$accion = $_GET['accion'] ?? '';
$dao = new IntervencionDAO();

// 2. ENRUTADOR PRINCIPAL (Alta Cohesión Funcional: Solo coordina, no procesa datos crudos)
try {
    switch ($accion) {
        case 'listar':
            echo json_encode($dao->obtenerTodos());
            break;

        case 'darDeAlta':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['patente'])) {
                throw new Exception("Patente no proporcionada para el alta.");
            }
            $exito = $dao->darDeAlta($data['patente']);
            echo json_encode(["success" => $exito]);
            break;

        case 'enviarATaller':
            // 1. Detectar el tipo de paquete entrante (Polimorfismo de red)
            $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

            if (strpos($contentType, 'application/json') !== false) {
                // A) Viene desde el botón amarillo "A Taller" (paquete JSON ligero)
                $data = json_decode(file_get_contents('php://input'), true);
                $patente = $data['patente'] ?? null;
                $detalle = $data['causa'] ?? 'Inspección solicitada desde tablero';
                $tipo = 'Preventivo';
                $costo = 0.00;
                $evidenciasStr = '';
            } else {
                // B) Viene desde el Modal de Intervención (paquete Multipart pesado)
                $evidenciasStr = procesarArchivosLocales($_FILES);
                $patente = $_POST['patente'] ?? null;
                $tipo = $_POST['tipo'] ?? 'Correctivo';
                $detalle = $_POST['detalle'] ?? '';
                $costo = floatval($_POST['costo'] ?? 0);
            }

            // 2. Validación centralizada
            if (empty($patente) || empty($detalle)) {
                throw new Exception("Datos obligatorios (patente o detalle) incompletos.");
            }

            // 3. Ejecución en la BD
            $exito = $dao->enviarATaller($patente, $tipo, $detalle, $costo, $evidenciasStr);
            echo json_encode(["success" => $exito]);
            break;

        default:
            http_response_code(400);
            echo json_encode(["error" => "Endpoint o acción no válida."]);
            break;
    }
} catch (Exception $e) {
    // Intercepción de fallos para que JavaScript no colapse por errores de PHP
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

// 3. AISLAMIENTO DE INFRAESTRUCTURA (File System)
function procesarArchivosLocales($archivosPeticion) {
    $evidenciasNombres = [];
    
    if (!empty($archivosPeticion['evidencias']['name'][0])) {
        $directorioDestino = __DIR__ . '/uploads/';
        
        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }
        
        foreach ($archivosPeticion['evidencias']['name'] as $key => $name) {
            if ($archivosPeticion['evidencias']['error'][$key] === UPLOAD_ERR_OK) {
                $tmpName = $archivosPeticion['evidencias']['tmp_name'][$key];
                
                // Sanitización estricta del nombre para evitar inyección de código (Path Traversal)
                $nombreSeguro = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', basename($name));
                $rutaFinal = $directorioDestino . $nombreSeguro;
                
                if (move_uploaded_file($tmpName, $rutaFinal)) {
                    $evidenciasNombres[] = $nombreSeguro;
                }
            }
        }
    }
    
    return implode(',', $evidenciasNombres); // Retorna "foto1.jpg,foto2.pdf" o un string vacío
}
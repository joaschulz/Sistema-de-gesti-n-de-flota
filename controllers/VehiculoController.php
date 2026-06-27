<?php
require_once '../dao/IntervencionDAO.php';

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

        // =======================================================
        // ENVIAR A TALLER (Notifica Telegram SOLO si viene de Alerta)
        // =======================================================
        case 'enviarATaller':
            require_once '../services/NotificacionService.php';

            $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
            $evidenciasNombres = [];
            $esPeticionJson = strpos($contentType, 'application/json') !== false;

            // 1. Extraemos la patente primero según el origen de la petición
            if ($esPeticionJson) {
                $data = json_decode(file_get_contents('php://input'), true);
                $patente = $data['patente'] ?? null;
            } else {
                $patente = $_POST['patente'] ?? null;
            }

            if (empty($patente)) {
                throw new Exception("Datos obligatorios incompletos (Falta patente).");
            }

            // 2. Consultamos la BD para traer el estado actual y sus NOVEDADES reales
            $datosBD = method_exists($dao, 'obtenerDatosVehiculo') ? $dao->obtenerDatosVehiculo($patente) : null;
            $estadoAnterior = $datosBD ? $datosBD['estado'] : 'Desconocido';
            $novedadReal = $datosBD ? $datosBD['novedades'] : '';

            // 3. Asignamos el detalle (motivo) dependiendo de cómo ingresó a taller
            if ($esPeticionJson) {
                // Si entra por el botón directo, el motivo ES la novedad de la base de datos
                $detalle = !empty($novedadReal) ? $novedadReal : 'Inspección solicitada sin novedades previas';
                $tipo = 'Preventivo';
                $costo = 0.00;
                $evidenciasStr = '';
            } else {
                // Si entra por el Modal, usa el texto y costo que el Jefe de Taller escribió
                $evidenciasStr = procesarArchivosLocales($_FILES, $evidenciasNombres);
                $tipo = $_POST['tipo'] ?? 'Correctivo';
                $detalle = $_POST['detalle'] ?? '';
                $costo = floatval($_POST['costo'] ?? 0);
            }

            if (empty($detalle)) {
                throw new Exception("Datos obligatorios incompletos (Falta detalle/motivo).");
            }

            // 4. Guardamos la intervención oficial en la BD
            $exito = $dao->enviarATaller($patente, $tipo, $detalle, $costo, $evidenciasStr);

            // 5. EVENTOS EXTERNOS (Telegram / Email)
            if ($exito) {
                try {
                    // TELEGRAM: Solo dispara si hay una TRANSICIÓN real (Evita el "Taller -> Taller")
                    if ($estadoAnterior !== 'Taller') {
                        NotificacionService::enviarAlertaTelegram($patente, $estadoAnterior, 'Taller', $detalle);
                    }
                    
                    // EMAIL (SMTP): Solo dispara si es un registro de INTERVENCIÓN (Viene del Modal / Formulario pesado)
                    if (!$esPeticionJson) {
                        NotificacionService::enviarReporteEmail($patente, $tipo, $detalle, $costo, $evidenciasNombres);
                    }
                } catch (Exception $e) {
                    // Si algo falla en la red externa, el servidor no colapsa
                    error_log("Fallo en notificación externa: " . $e->getMessage());
                }
            }

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
function procesarArchivosLocales($archivosPeticion, &$evidenciasNombres) {
    $evidenciasNombres = [];
    
    if (!empty($archivosPeticion['evidencias']['name'][0])) {
        $directorioDestino = __DIR__ . '/../assets/uploads/';
        
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
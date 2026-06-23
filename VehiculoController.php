<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'IntervencionDAO.php';

$dao = new IntervencionDAO();
$metodo = $_SERVER['REQUEST_METHOD'];

// Identificar qué acción quiere hacer el cliente (Frontend)
$accion = $_GET['accion'] ?? 'listar';

if ($metodo === 'GET' && $accion === 'listar') {
    $lista = $dao->obtenerTodos();
    echo json_encode($lista);
    exit;
}

if ($metodo === 'POST') {
    // Leer el JSON asincrónico que viene del Frontend
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($accion === 'enviarATaller') {
        $patente = $_POST['patente'] ?? '';
        $tipo = $_POST['tipo'] ?? 'Correctivo';
        $detalle = $_POST['detalle'] ?? '';
        $costo = $_POST['costo'] ?? 0.00;
        
        // 1. Manejo y guardado de archivos
        $evidenciasNombres = [];
        if (!empty($_FILES['evidencias']['name'][0])) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            foreach ($_FILES['evidencias']['name'] as $key => $name) {
                if ($_FILES['evidencias']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['evidencias']['tmp_name'][$key];
                    // Sanitización del nombre del archivo
                    $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', basename($name));
                    $dest = $uploadDir . $safeName;
                    
                    if (move_uploaded_file($tmpName, $dest)) {
                        $evidenciasNombres[] = $safeName;
                    }
                }
            }
        }
        
        // Convertimos el array de nombres en un string separado por comas
        $evidenciasStr = implode(',', $evidenciasNombres);
        
        // 2. Insertar en Base de Datos
        if (!empty($patente) && !empty($detalle)) {
            $exito = $dao->enviarATaller($patente, $tipo, $detalle, $costo, $evidenciasStr);
            echo json_encode(["success" => $exito]);
        } else {
            echo json_encode(["success" => false, "error" => "Datos incompletos"]);
        }
        exit;
    }

    if ($accion === 'darDeAlta') {
        $patente = $input['patente'] ?? '';
        
        if (!empty($patente)) {
            $exito = $dao->darDeAlta($patente);
            echo json_encode(["success" => $exito]);
        } else {
            echo json_encode(["success" => false, "error" => "Falta la patente"]);
        }
        exit;
    }
}

// Si la ruta no coincide con nada
echo json_encode(["error" => "Endpoint no encontrado"]);
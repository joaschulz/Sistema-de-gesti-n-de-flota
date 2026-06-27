<?php
session_start();
require_once '../dao/UsuarioDAO.php';

header('Content-Type: application/json; charset=utf-8');

$accion = $_GET['accion'] ?? '';

try {
    $dao = new UsuarioDAO();

    switch ($accion) {
        case 'login':
            $data = json_decode(file_get_contents('php://input'), true);
            $usuario = trim($data['usuario'] ?? '');
            $password = $data['password'] ?? '';

            if (empty($usuario) || empty($password)) {
                echo json_encode(['success' => false, 'error' => 'Por favor, complete todos los campos.']);
                exit;
            }

            $userRecord = $dao->obtenerPorUsuario($usuario);

            if ($userRecord && password_verify($password, $userRecord['password'])) {
                $rolActual = $userRecord['rol'];

                // --- DICCIONARIO DE RUTAS (Abierto a la extensión) ---
                $tablaRutas = [
                    'JefeTaller' => 'JefeTaller.php',
                    'PersonalIT' => 'PanelIT.php'
                    // 'Admin' => 'Gerente.php',         // Descomentar en el próximo TP
                    // 'PersonalIT' => 'PanelIT.php'     // Dejado para el final
                ];

                // Si el rol aún no tiene un panel desarrollado en este MVP, lo frenamos
                if (!array_key_exists($rolActual, $tablaRutas)) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => "Fase de Desarrollo: El entorno para tu rol ($rolActual) aún no está disponible."]);
                    exit;
                }

                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $userRecord['id'];
                $_SESSION['usuario_nombre'] = $userRecord['usuario'];
                $_SESSION['usuario_rol'] = $rolActual;
                
                // El servidor le dice al JS a dónde debe viajar
                echo json_encode(['success' => true, 'redirect' => $tablaRutas[$rolActual]]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Usuario o contraseña incorrectos.']);
            }
            break;

        case 'verificar':
            // Como la seguridad estricta ya la hace PHP al cargar la página,
            // aquí solo devolvemos los datos para que JS dibuje el nombre.
            if (isset($_SESSION['usuario_id'])) {
                echo json_encode([
                    'autenticado' => true,
                    'usuario' => $_SESSION['usuario_nombre'],
                    'rol' => $_SESSION['usuario_rol']
                ]);
            } else {
                echo json_encode(['autenticado' => false]);
            }
            break;

        case 'logout':
            // Destruye la sesión
            session_unset();
            session_destroy();
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Acción no válida.']);
            break;
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno del servidor de autenticación.']);
}
?>
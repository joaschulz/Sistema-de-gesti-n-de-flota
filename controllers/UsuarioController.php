<?php
require_once __DIR__ . '/../config/Seguridad.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'PersonalIT') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado.']);
    exit;
}

require_once __DIR__ . '/../dao/UsuarioDAO.php';
header('Content-Type: application/json; charset=utf-8');

$accion = $_GET['accion'] ?? '';
$dao = new UsuarioDAO();

try {
    switch ($accion) {
        case 'listar':
            echo json_encode($dao->obtenerTodos());
            break;

        case 'crear':
            $data = json_decode(file_get_contents('php://input'), true);
            $usuario = trim($data['usuario'] ?? '');
            $password = $data['password'] ?? '';
            $rol = $data['rol'] ?? 'PersonalCampo';

            if (empty($usuario) || empty($password)) {
                echo json_encode(['success' => false, 'error' => 'Complete todos los campos.']);
                exit;
            }
            if ($dao->obtenerPorUsuario($usuario)) {
                echo json_encode(['success' => false, 'error' => 'El usuario ya existe.']);
                exit;
            }
            echo json_encode(['success' => $dao->crear($usuario, $password, $rol)]);
            break;

        case 'modificarRol':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id'] ?? 0);
            $nuevoRol = $data['rol'] ?? '';

            if ($id === intval($_SESSION['usuario_id'])) {
                echo json_encode(['success' => false, 'error' => 'No puedes cambiar tu propio rol.']);
                exit;
            }

            // REGLA ANTI-LOCKOUT: Prevenir degradación del último IT
            $usuarioTarget = $dao->obtenerPorId($id);
            if ($usuarioTarget && $usuarioTarget['rol'] === 'PersonalIT' && $nuevoRol !== 'PersonalIT') {
                if ($dao->contarPorRol('PersonalIT') <= 1) {
                    echo json_encode(['success' => false, 'error' => 'Debe existir al menos un Personal IT activo en el sistema.']);
                    exit;
                }
            }
            echo json_encode(['success' => $dao->actualizarRol($id, $nuevoRol)]);
            break;

        case 'modificarEstado':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id'] ?? 0);
            $nuevoEstado = $data['estado'] ?? '';

            if ($id === intval($_SESSION['usuario_id'])) {
                echo json_encode(['success' => false, 'error' => 'No puedes suspender tu propia cuenta.']);
                exit;
            }

            // REGLA ANTI-LOCKOUT: Prevenir suspensión del último IT
            $usuarioTarget = $dao->obtenerPorId($id);
            if ($usuarioTarget && $usuarioTarget['rol'] === 'PersonalIT' && $nuevoEstado === 'Suspendido') {
                if ($dao->contarPorRol('PersonalIT') <= 1) {
                    echo json_encode(['success' => false, 'error' => 'No puedes suspender al último Personal IT del sistema.']);
                    exit;
                }
            }
            echo json_encode(['success' => $dao->actualizarEstado($id, $nuevoEstado)]);
            break;

        case 'resetearContrasena':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id'] ?? 0);
            $nuevaPass = $data['password'] ?? '';

            if (empty($nuevaPass)) {
                echo json_encode(['success' => false, 'error' => 'La contraseña no puede estar vacía.']);
                exit;
            }
            echo json_encode(['success' => $dao->resetearPassword($id, $nuevaPass)]);
            break;

        // CASO RESTAURADO Y BLINDADO
        case 'eliminar':
            $id = intval($_GET['id'] ?? 0);

            if ($id === intval($_SESSION['usuario_id'])) {
                echo json_encode(['success' => false, 'error' => 'No puedes eliminar tu propia cuenta.']);
                exit;
            }

            // REGLA ANTI-LOCKOUT: Prevenir eliminación del último IT
            $usuarioTarget = $dao->obtenerPorId($id);
            if ($usuarioTarget && $usuarioTarget['rol'] === 'PersonalIT') {
                if ($dao->contarPorRol('PersonalIT') <= 1) {
                    echo json_encode(['success' => false, 'error' => 'Operación Denegada: El sistema no puede quedarse sin administradores IT.']);
                    exit;
                }
            }

            echo json_encode(['success' => $dao->eliminar($id)]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Acción no válida.']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
<?php
class Seguridad {
    public static function protegerVista($rolesPermitidos) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Autenticación básica (¿Inició sesión?)
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: login.html");
            exit;
        }

        // 2. Autorización por Rol (¿Tiene permiso para esta vista?)
        $rolActual = $_SESSION['usuario_rol'] ?? '';
        if (!in_array($rolActual, $rolesPermitidos)) {
            // Si es un intruso, destruimos la sesión y lo pateamos con mensaje
            session_unset();
            session_destroy();
            header("Location: login.html?error=denegado");
            exit;
        }
    }
}
?>
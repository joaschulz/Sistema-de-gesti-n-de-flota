<?php
require_once __DIR__ . '/../config/Conexion.php';

class UsuarioDAO {
    
    public function obtenerPorUsuario($nombreUsuario) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("SELECT id, usuario, nombre, apellido, password, rol, estado FROM usuarios WHERE usuario = :usuario");
            $stmt->execute([':usuario' => $nombreUsuario]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en UsuarioDAO::obtenerPorUsuario: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorLegajo($legajo) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE legajo = :legajo");
            $stmt->execute([':legajo' => $legajo]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // --- NUEVOS MÉTODOS DE REGLAS DE NEGOCIO (ANTI-LOCKOUT) ---
    public function obtenerPorId($id) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("SELECT id, usuario, rol, estado FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function contarPorRol($rol) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE rol = :rol AND estado = 'Activo'");
            $stmt->execute([':rol' => $rol]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
    // -----------------------------------------------------------

    public function obtenerTodos() {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->query("SELECT id, usuario, nombre, apellido, legajo, rol, estado, ultimo_acceso, fecha_creacion FROM usuarios ORDER BY id ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function crear($usuario, $nombre, $apellido, $legajo, $password, $rol) {
        try {
            $pdo = Conexion::conectar();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, nombre, apellido, legajo, password, rol, estado) VALUES (:usuario, :nombre, :apellido, :legajo, :password, :rol, 'Activo')");
            return $stmt->execute([
                ':usuario' => $usuario, 
                ':nombre' => $nombre, 
                ':apellido' => $apellido, 
                ':legajo' => $legajo, 
                ':password' => $hash, 
                ':rol' => $rol
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarRol($id, $nuevoRol) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("UPDATE usuarios SET rol = :rol WHERE id = :id");
            return $stmt->execute([':rol' => $nuevoRol, ':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarEstado($id, $nuevoEstado) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("UPDATE usuarios SET estado = :estado WHERE id = :id");
            return $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function resetearPassword($id, $nuevaPassword) {
        try {
            $pdo = Conexion::conectar();
            $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
            return $stmt->execute([':password' => $hash, ':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminar($id) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function registrarAcceso($id) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
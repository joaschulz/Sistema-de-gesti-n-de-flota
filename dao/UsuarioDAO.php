<?php
require_once __DIR__ . '/../config/Conexion.php';

class UsuarioDAO {
    
    public function obtenerPorUsuario($nombreUsuario) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("SELECT u.ID_usuario, u.usuario, u.nombre, u.apellido, u.contrasena, r.nombre_rol as rol, u.estado FROM USUARIO u JOIN ROL r ON u.ID_rol = r.ID_rol WHERE u.usuario = :usuario");
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
            $stmt = $pdo->prepare("SELECT ID_usuario FROM USUARIO WHERE legajo = :legajo");
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
            $stmt = $pdo->prepare("SELECT u.ID_usuario, u.usuario, r.nombre_rol as rol, u.estado FROM USUARIO u JOIN ROL r ON u.ID_rol = r.ID_rol WHERE u.ID_usuario = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function contarPorRol($rol) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM USUARIO u JOIN ROL r ON u.ID_rol = r.ID_rol WHERE r.nombre_rol = :rol AND u.estado = 'Activo'");
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
            $stmt = $pdo->query("SELECT u.ID_usuario as id, u.usuario, u.nombre, u.apellido, u.legajo, r.nombre_rol as rol, u.estado, u.ultimo_acceso, u.fecha_creacion FROM USUARIO u JOIN ROL r ON u.ID_rol = r.ID_rol ORDER BY u.ID_usuario ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function crear($usuario, $nombre, $apellido, $legajo, $password, $rol) {
        try {
            $pdo = Conexion::conectar();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO USUARIO (usuario, nombre, apellido, legajo, contrasena, ID_rol, estado) VALUES (:usuario, :nombre, :apellido, :legajo, :contrasena, (SELECT ID_rol FROM ROL WHERE nombre_rol = :rol), 'Activo')");
            return $stmt->execute([
                ':usuario' => $usuario, 
                ':nombre' => $nombre, 
                ':apellido' => $apellido, 
                ':legajo' => $legajo, 
                ':contrasena' => $hash, 
                ':rol' => $rol
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarRol($id, $nuevoRol) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("UPDATE USUARIO SET ID_rol = (SELECT ID_rol FROM ROL WHERE nombre_rol = :rol) WHERE ID_usuario = :id");
            return $stmt->execute([':rol' => $nuevoRol, ':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarEstado($id, $nuevoEstado) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("UPDATE USUARIO SET estado = :estado WHERE ID_usuario = :id");
            return $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function resetearPassword($id, $nuevaPassword) {
        try {
            $pdo = Conexion::conectar();
            $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE USUARIO SET contrasena = :contrasena WHERE ID_usuario = :id");
            return $stmt->execute([':contrasena' => $hash, ':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminar($id) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("DELETE FROM USUARIO WHERE ID_usuario = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function registrarAcceso($id) {
        try {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare("UPDATE USUARIO SET ultimo_acceso = NOW() WHERE ID_usuario = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
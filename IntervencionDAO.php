<?php
require_once 'Conexion.php';

class IntervencionDAO {
    public function obtenerTodos() {
        $pdo = Conexion::conectar();
        
        // Hacemos que la consulta SQL traduzca los nombres de tus columnas
        // para que el JavaScript las entienda sin tener que modificarlo.
        $sql = "SELECT v.patente, v.marca, v.modelo, v.kilometraje, v.novedades, 
                       IF(v.estado = 'Taller', 'En Taller', v.estado) as estado, 
                       i.detalle as causa 
                FROM vehiculos v 
                LEFT JOIN intervenciones i ON v.patente = i.patente_vehiculo 
                ORDER BY v.estado ASC";
                
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function enviarATaller($patente, $tipo, $detalle, $costo, $evidenciasStr) {
        $pdo = Conexion::conectar();
        try {
            $pdo->beginTransaction();
            
            // 1. Actualizamos el estado del vehículo
            $sql1 = "UPDATE vehiculos SET estado = 'Taller' WHERE patente = :patente";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute([':patente' => $patente]);

            // 2. Insertamos la intervención INCLUYENDO EL COSTO
            $sql2 = "INSERT INTO intervenciones (patente_vehiculo, tipo, costo, detalle, evidencias) VALUES (:patente, :tipo, :costo, :detalle, :evidencias)";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([
                ':patente' => $patente, 
                ':tipo' => $tipo,
                ':costo' => $costo,
                ':detalle' => $detalle,
                ':evidencias' => $evidenciasStr
            ]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function darDeAlta($patente) {
        $pdo = Conexion::conectar();
        try {
            $pdo->beginTransaction();

            // 1. Volver a Operativo
            $sql1 = "UPDATE vehiculos SET estado = 'Operativo' WHERE patente = :patente";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute([':patente' => $patente]);

            // 2. Eliminar la intervención activa
            $sql2 = "DELETE FROM intervenciones WHERE patente_vehiculo = :patente";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([':patente' => $patente]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }
}
<?php
require_once 'Conexion.php';

class IntervencionDAO {
    
    // 1. LISTAR UNIDADES EN EL TABLERO KANBAN
    public function obtenerTodos() {
        $pdo = Conexion::conectar();
        
        // Uso de Subquery y COALESCE para evitar duplicación de filas y garantizar Cohesión de Datos
        $sql = "SELECT v.patente, v.marca, v.modelo, v.kilometraje, v.novedades, 
                       IF(v.estado = 'Taller', 'En Taller', v.estado) as estado, 
                       COALESCE(
                           (SELECT i.detalle FROM intervenciones i WHERE i.patente_vehiculo = v.patente ORDER BY i.id DESC LIMIT 1),
                           v.novedades,
                           'Falla o revisión general sin especificar'
                       ) as causa 
                FROM vehiculos v 
                ORDER BY v.estado ASC";
                
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. REGISTRAR INTERVENCIÓN / ENVIAR A TALLER
    public function enviarATaller($patente, $tipo, $detalle, $costo, $evidenciasStr) {
        $pdo = Conexion::conectar();
        try {
            $pdo->beginTransaction();
            
            // Transición de estado de la unidad
            $sql1 = "UPDATE vehiculos SET estado = 'Taller' WHERE patente = :patente";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute([':patente' => $patente]);

            // Persistencia de la intervención histórica (No destructiva)
            $sql2 = "INSERT INTO intervenciones (patente_vehiculo, tipo, costo, detalle, evidencias) 
                     VALUES (:patente, :tipo, :costo, :detalle, :evidencias)";
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

    // 3. DAR DE ALTA (Mantiene consistencia de pantalla y respeta el histórico de intervenciones)
    public function darDeAlta($patente) {
        $pdo = Conexion::conectar();
        try {
            $pdo->beginTransaction();

            // Limpiamos la novedad actual del vehículo para dejarlo libre en el Kanban
            $sql = "UPDATE vehiculos SET estado = 'Operativo', novedades = NULL WHERE patente = :patente";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':patente' => $patente]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }
}
<?php
require_once 'Conexion.php';

class IntervencionDAO {
    
    // ========================================================
    // 1. MÉTODO DE CONSULTA: OBTENER DATOS ACTUALES DE UN AUTO
    // ========================================================
    // (Alineado al Controlador para extraer el estado anterior y la novedad real)
    public function obtenerDatosVehiculo($patente) {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT estado, novedades FROM vehiculos WHERE patente = :patente");
        $stmt->execute([':patente' => $patente]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // ========================================================
    // 2. MÉTODO DE RENDERIZADO: LISTAR UNIDADES EN EL KANBAN
    // ========================================================
    // (Corregido: Lee v.novedades directamente para evitar fantasmas históricos)
    public function obtenerTodos() {
        $pdo = Conexion::conectar();
        
        $sql = "SELECT v.patente, v.marca, v.modelo, v.kilometraje, v.novedades, 
                       IF(v.estado = 'Taller', 'En Taller', v.estado) as estado, 
                       COALESCE(v.novedades, 'Falla o revisión general sin especificar') as causa 
                FROM vehiculos v 
                ORDER BY v.estado ASC";
                
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

// ========================================================
    // 3. MÉTODO TRANSACCIONAL: REGISTRAR INGRESO / ORDEN DE TRABAJO
    // ========================================================
    public function enviarATaller($patente, $tipo, $detalle, $costo, $evidenciasStr) {
        $pdo = Conexion::conectar();
        try {
            $pdo->beginTransaction();
            
            // así el tablero sigue mostrando la causa original intacta.
            $sql1 = "UPDATE vehiculos SET estado = 'Taller' WHERE patente = :patente";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute([':patente' => $patente]);

            // El detalle de la reparación va exclusivamente al historial
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

    // ========================================================
    // 4. MÉTODO TRANSACCIONAL: DAR DE ALTA UNIDAD DE TALLER
    // ========================================================
    // (Consistencia de UI: Pasa a Operativo y limpia observaciones sin borrar el histórico)
    public function darDeAlta($patente) {
        $pdo = Conexion::conectar();
        try {
            $pdo->beginTransaction();

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
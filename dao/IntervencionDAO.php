<?php
require_once '../config/Conexion.php';

class IntervencionDAO {
    
    // ========================================================
    // 1. MÉTODO DE CONSULTA: OBTENER DATOS ACTUALES DE UN AUTO
    // ========================================================
    // (Alineado al Controlador para extraer el estado anterior y la novedad real)
    public function obtenerDatosVehiculo($patente) {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT estado, novedades FROM VEHICULO WHERE patente = :patente");
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
                FROM VEHICULO v 
                ORDER BY v.estado ASC";
                
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

// ========================================================
    // 3. MÉTODO TRANSACCIONAL: REGISTRAR INGRESO / ORDEN DE TRABAJO
    // ========================================================
    public function enviarATaller($patente, $tipoMantenimiento, $tipoFalla, $detalle, $costo, $listaEvidencias, $idUsuario) {
        $pdo = Conexion::conectar();
        try {
            $pdo->beginTransaction();
            
            $sql1 = "UPDATE VEHICULO SET estado = 'Taller' WHERE patente = :patente";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute([':patente' => $patente]);

            $sql2 = "INSERT INTO INTERVENCION_TECNICA (fecha_inicio, costo, tipo_mantenimiento, tipo_falla, detalles, ID_usuario, patente) 
                     VALUES (NOW(), :costo, :tipoMantenimiento, :tipoFalla, :detalle, :idUsuario, :patente)";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([
                ':costo' => $costo,
                ':tipoMantenimiento' => $tipoMantenimiento,
                ':tipoFalla' => $tipoFalla,
                ':detalle' => $detalle,
                ':idUsuario' => $idUsuario,
                ':patente' => $patente
            ]);
            $idIntervencion = $pdo->lastInsertId();

            if (!empty($listaEvidencias)) {
                $sql3 = "INSERT INTO EVIDENCIA_DIGITAL (url_archivo, tipo_archivo, ID_intervencion) VALUES (:url, :tipoArch, :idInt)";
                $stmt3 = $pdo->prepare($sql3);
                foreach ($listaEvidencias as $evidencia) {
                    $stmt3->execute([
                        ':url' => 'assets/uploads/' . $evidencia,
                        ':tipoArch' => 'image/*', 
                        ':idInt' => $idIntervencion
                    ]);
                }
            }

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error en enviarATaller: " . $e->getMessage());
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

            $sql = "UPDATE VEHICULO SET estado = 'Operativo', novedades = NULL WHERE patente = :patente";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':patente' => $patente]);

            $sql2 = "UPDATE INTERVENCION_TECNICA SET fecha_fin = NOW() WHERE patente = :patente AND fecha_fin IS NULL";
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
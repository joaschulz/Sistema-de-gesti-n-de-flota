<?php
class NotificacionService {
    
    // ⚠️ COLOCÁ TU TOKEN REAL DE BOTFAHER ACÁ
    private static $telegramToken = ''; 
    
    // ⚠️ COLOCÁ TU CHAT ID REAL DE RAWDATABOT ACÁ
    private static $telegramChatId = '';
    
    private static $emailDestino = '@gmail.com';

    /**
     * Envía notificaciones estrictas de transición de estado
     */
    public static function enviarAlertaTelegram($patente, $estadoAnterior, $estadoNuevo, $motivo) {
        $mensaje = "🚨 *ALERTA DE FLOTA CELO* 🚨\n\n";
        $mensaje .= "🚗 *Unidad:* " . $patente . "\n";
        $mensaje .= "🔄 *Transición:* " . $estadoAnterior . " ➡️ " . $estadoNuevo . "\n";
        $mensaje .= "📋 *Motivo:* " . $motivo;

        $url = "https://api.telegram.org/bot" . self::$telegramToken . "/sendMessage";
        
        $data = [
            'chat_id' => self::$telegramChatId,
            'text' => $mensaje,
            'parse_mode' => 'Markdown'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Envía el reporte técnico formal por correo
     */
    public static function enviarReporteEmail($patente, $tipo, $detalle, $costo, $evidenciasNombres) {
        $asunto = "NUEVA INTERVENCION REGISTRADA - Unidad " . $patente;
        
        $mensajeHtml = "<h2>Reporte de Taller - Flota CELO</h2>";
        $mensajeHtml .= "<p><strong>Patente:</strong> " . $patente . "</p>";
        $mensajeHtml .= "<p><strong>Tipo de Mantenimiento:</strong> " . $tipo . "</p>";
        $mensajeHtml .= "<p><strong>Costo Estimado:</strong> $" . number_format($costo, 2) . "</p>";
        $mensajeHtml .= "<p><strong>Detalle Técnico:</strong> " . $detalle . "</p>";
        
        if (!empty($evidenciasNombres)) {
            $mensajeHtml .= "<h3>Evidencias Adjuntas:</h3><ul>";
            foreach ($evidenciasNombres as $archivo) {
                $urlArchivo = "http://localhost/gestion-flota/uploads/" . $archivo;
                $mensajeHtml .= "<li><a href='" . $urlArchivo . "'>Ver " . $archivo . "</a></li>";
            }
            $mensajeHtml .= "</ul>";
        }

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Sistema CELO Fleet <no-reply@celo.com>\r\n";

        @mail(self::$emailDestino, $asunto, $mensajeHtml, $headers);
    }
}
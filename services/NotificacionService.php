<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../vendor/src/Exception.php';
require_once __DIR__ . '/../vendor/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/src/SMTP.php';

class NotificacionService {
    
    // ⚠️ COLOCÁ TU TOKEN REAL DE BOTFAHER ACÁ
    private static $telegramToken = ''; 
    
    // ⚠️ COLOCÁ LOS CHAT IDs A LOS QUE QUIERAS NOTIFICAR (separados por coma)
    private static $telegramChatIds = ['', '']; 
    
    private static $emailDestino = '';

    /**
     * Envía notificaciones estrictas de transición de estado
     */
    public static function enviarAlertaTelegram($patente, $estadoAnterior, $estadoNuevo, $motivo) {
        $mensaje = "🚨 *ALERTA DE FLOTA CELO* 🚨\n\n";
        $mensaje .= "🚗 *Unidad:* " . $patente . "\n";
        $mensaje .= "🔄 *Transición:* " . $estadoAnterior . " ➡️ " . $estadoNuevo . "\n";
        $mensaje .= "📋 *Motivo:* " . $motivo;

        $url = "https://api.telegram.org/bot" . self::$telegramToken . "/sendMessage";
        
        foreach (self::$telegramChatIds as $chatId) {
            if (empty(trim($chatId)) || $chatId === 'ID_DEL_SEGUNDO_CHAT') continue;

            $data = [
                'chat_id' => $chatId,
                'text' => $mensaje,
                'parse_mode' => 'Markdown'
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            curl_close($ch);
        }
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
                $urlArchivo = "http://localhost/gestion-flota/assets/uploads/" . $archivo;
                $mensajeHtml .= "<li><a href='" . $urlArchivo . "'>Ver " . $archivo . "</a></li>";
            }
            $mensajeHtml .= "</ul>";
        }

        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP (Ajustar con credenciales reales)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';                     // Servidor SMTP (ej: smtp.gmail.com)
            $mail->SMTPAuth   = true;                                 // Habilitar autenticación SMTP
            $mail->Username   = '';                // Nombre de usuario SMTP
            $mail->Password   = '';        // Contraseña de aplicación SMTP
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       // Habilitar cifrado TLS
            $mail->Port       = 587;                              // Puerto SMTP

            // Destinatarios
            $mail->setFrom('', 'Sistema CELO Fleet');
            $mail->addAddress(self::$emailDestino);

            // Adjuntar archivos de evidencias
            if (!empty($evidenciasNombres)) {
                foreach ($evidenciasNombres as $archivo) {
                    $rutaArchivo = __DIR__ . '/../assets/uploads/' . $archivo;
                    if (file_exists($rutaArchivo)) {
                        $mail->addAttachment($rutaArchivo, $archivo);
                    }
                }
            }

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $mensajeHtml;
            $mail->CharSet = 'UTF-8';

            $mail->send();
        } catch (Exception $e) {
            error_log("Error de PHPMailer: " . $mail->ErrorInfo);
        }
    }
}
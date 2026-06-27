<?php
// Apuntamos a la carpeta config
require_once 'config/Conexion.php';

try {
    $pdo = Conexion::conectar();
    
    // 1. Limpiamos la tabla para no tener usuarios duplicados o rotos
    $pdo->exec("TRUNCATE TABLE usuarios");

    // 2. Generamos el Hash REAL usando TU propio servidor PHP
    $passwordReal = 'password123';
    $hashSeguro = password_hash($passwordReal, PASSWORD_DEFAULT);

    // 3. Insertamos los 4 usuarios de prueba con el hash correcto
    $sql = "INSERT INTO usuarios (usuario, nombre, apellido, legajo, password, rol) VALUES 
            ('chofer_juan', 'Juan', 'Perez', 'L-1001', '$hashSeguro', 'PersonalCampo'),
            ('jefe_pedro', 'Pedro', 'Gomez', 'L-2001', '$hashSeguro', 'JefeTaller'),
            ('admin_celo', 'Carlos', 'Lopez', 'L-3001', '$hashSeguro', 'Admin'),
            ('it_soporte', 'Ana', 'Martinez', 'L-9001', '$hashSeguro', 'PersonalIT')";
    
    $pdo->exec($sql);
    
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h2 style='color: green;'>✅ Usuarios creados con éxito</h2>";
    echo "<p>La contraseña para todos es: <b>password123</b></p>";
    echo "<a href='login.html' style='padding: 10px 20px; background: #030213; color: white; text-decoration: none; border-radius: 5px;'>Ir al Login</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "Error de base de datos: " . $e->getMessage();
}
?>
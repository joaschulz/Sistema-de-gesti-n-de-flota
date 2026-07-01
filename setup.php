<?php
// Apuntamos a la carpeta config
require_once 'config/Conexion.php';

try {
    $pdo = Conexion::conectar();
    
    // 1. Limpiamos las tablas (desactivando FK checks temporalmente si es necesario)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE USUARIO");
    $pdo->exec("TRUNCATE TABLE ROL");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 2. Generamos el Hash REAL usando TU propio servidor PHP
    $passwordReal = 'password123';
    $hashSeguro = password_hash($passwordReal, PASSWORD_DEFAULT);

    // 3. Insertamos los roles
    $sqlRoles = "INSERT INTO ROL (nombre_rol, permisos) VALUES 
                 ('JefeTaller', 'ALL_MAINTENANCE'),
                 ('Chofer', 'READ_VEHICLES, WRITE_REPORTS'),
                 ('Admin', 'ALL_SYSTEM'),
                 ('PersonalIT', 'SYSTEM_SUPPORT')";
    $pdo->exec($sqlRoles);

    // 4. Insertamos los 4 usuarios de prueba con el hash correcto y su ID_rol
    $sqlUsuarios = "INSERT INTO USUARIO (usuario, legajo, nombre, apellido, contrasena, estado, ID_rol) VALUES 
            ('jefe_pedro', 1001, 'Pedro', 'Gómez', '$hashSeguro', 'Activo', 1),
            ('chofer_juan', 1002, 'Juan', 'Pérez', '$hashSeguro', 'Activo', 2),
            ('admin_celo', 1003, 'Ana', 'López', '$hashSeguro', 'Activo', 3),
            ('it_soporte', 1004, 'Laura', 'Martinez', '$hashSeguro', 'Activo', 4)";
    
    $pdo->exec($sqlUsuarios);
    
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h2 style='color: green;'>✅ Usuarios creados con éxito</h2>";
    echo "<p>La contraseña para todos es: <b>password123</b></p>";
    echo "<a href='login.html' style='padding: 10px 20px; background: #030213; color: white; text-decoration: none; border-radius: 5px;'>Ir al Login</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "Error de base de datos: " . $e->getMessage();
}
?>
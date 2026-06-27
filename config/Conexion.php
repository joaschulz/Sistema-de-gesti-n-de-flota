<?php
class Conexion {
    public static function conectar() {
        $host = 'localhost';
        $db   = 'celo_fleet';
        $user = 'root';
        $pass = 'admin';
        $port = '3306'; 
        
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Error de conexión al puerto $port: " . $e->getMessage());
        }
    }
}
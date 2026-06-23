<?php
class Conexion {
    private static $host = 'localhost';
    private static $db = 'celo_fleet';
    private static $user = 'root'; 
    private static $pass = 'admin';
    private static $pdo = null;

    public static function conectar() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=utf8", self::$user, self::$pass);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die(json_encode(["error" => "Error de conexión: " . $e->getMessage()]));
            }
        }
        return self::$pdo;
    }
}
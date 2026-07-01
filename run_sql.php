<?php require_once 'config/Conexion.php'; $pdo = Conexion::conectar(); $pdo->exec(file_get_contents('assets/celo_fleet.sql')); echo 'Done';

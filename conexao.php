<?php
date_default_timezone_set('America/Sao_Paulo');
$host = 'localhost';
$db = 'wwasse_ouvidoria';
$user = 'wwasse_ouvidoria';
$pass = '{:Qc.:5D9<?}<1slK5{ZQL>_385}:fT~XrI1G_.-%3_m/#\CX4';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit("Erro de conexão: " . $e->getMessage());
}

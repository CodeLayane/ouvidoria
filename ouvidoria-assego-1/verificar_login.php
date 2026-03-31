<?php
date_default_timezone_set('America/Sao_Paulo');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$nivel_acesso = $_SESSION['nivel_acesso'];
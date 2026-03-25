<?php
require "../includes/bootstrap.php";

verificarLogin();
validarPost();
validarCSRF($_POST['csrf_token'] ?? '');


$faixa_id = (int) ($_POST['faixa_id'] ?? 0);
$usuario_id = $_SESSION['usuario_id'];

try {
    registrarReproducao($pdo, $usuario_id, $faixa_id);
} catch (Exception $e) {
    exit($e->getMessage());
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();

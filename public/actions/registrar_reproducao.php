<?php

require __DIR__ . '/../../app/includes/bootstrap.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

verificarLogin();
validarPost();
validarCSRF($_POST['csrf_token'] ?? '');

$faixa_id = (int) ($_POST['faixa_id'] ?? 0);
$usuario_id = $_SESSION['usuario_id'];

if ($faixa_id <= 0) {
    http_response_code(400);
    echo json_encode(["erro" => "ID inválido"]);
    exit;
}

registrarReproducao($pdo, $usuario_id, $faixa_id);

header('Content-Type: application/json');
echo json_encode(["status" => "ok"]);

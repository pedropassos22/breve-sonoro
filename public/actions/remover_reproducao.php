<?php
require __DIR__ . '/../../app/includes/bootstrap.php';

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

$stmt = $pdo->prepare("
    DELETE FROM reproducoes
    WHERE usuario_id = ? AND faixa_id = ?
    LIMIT 1
");

$stmt->execute([$usuario_id, $faixa_id]);

header('Content-Type: application/json');
echo json_encode(["status" => "ok"]);

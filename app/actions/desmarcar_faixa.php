<?php
require "includes/config.php";
require "includes/session.php";

verificarLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Método não permitido.");
}

validarCSRF($_POST['csrf_token'] ?? '');

$faixa_id = (int) ($_POST['faixa_id'] ?? 0);

if ($faixa_id <= 0) {
    exit("ID inválido.");
}

$stmt = $pdo->prepare("
    DELETE FROM reproducoes
    WHERE usuario_id = ? AND faixa_id = ?
    LIMIT 1
");

$stmt->execute([$_SESSION['usuario_id'], $faixa_id]);

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();

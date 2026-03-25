<?php
require "../includes/bootstrap.php";

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

// Verificar se já existe avaliação
$stmt = $pdo->prepare("
    SELECT favorita FROM avaliacoes
    WHERE usuario_id = ? AND faixa_id = ?
");

$stmt->execute([$usuario_id, $faixa_id]);
$avaliacao = $stmt->fetch(PDO::FETCH_ASSOC);

if ($avaliacao) {

    $nova = $avaliacao['favorita'] ? 0 : 1;

    $stmt = $pdo->prepare("
        UPDATE avaliacoes
        SET favorita = ?
        WHERE usuario_id = ? AND faixa_id = ?
    ");

    $stmt->execute([$nova, $usuario_id, $faixa_id]);

} else {

    $stmt = $pdo->prepare("
        INSERT INTO avaliacoes (usuario_id, faixa_id, favorita)
        VALUES (?, ?, 1)
    ");

    $stmt->execute([$usuario_id, $faixa_id]);
}

echo json_encode(["status" => "ok"]);

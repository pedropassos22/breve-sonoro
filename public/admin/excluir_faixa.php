<?php
require "../includes/config.php";
require "../includes/session.php";

verificarAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Método não permitido.");
}

validarCSRF($_POST['csrf_token'] ?? '');

$faixaId = (int) ($_POST['faixa_id'] ?? 0);
$albumId = (int) ($_POST['album_id'] ?? 0);

if ($faixaId <= 0 || $albumId <= 0) {
    exit("ID inválido.");
}

// Verifica se a faixa existe e pertence ao álbum informado
$stmt = $pdo->prepare("SELECT id FROM faixas WHERE id = ? AND album_id = ?");
$stmt->execute([$faixaId, $albumId]);

if (!$stmt->fetch()) {
    exit("Faixa não encontrada.");
}

// Exclui
$stmt = $pdo->prepare("DELETE FROM faixas WHERE id = ?");
$stmt->execute([$faixaId]);

header("Location: ../nova_faixa.php?album_id=" . $albumId);
exit();

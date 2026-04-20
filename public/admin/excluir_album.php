<?php
require __DIR__ . '/../../app/includes/bootstrap.php';

verificarLogin();
verificarAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

validarCSRF($_POST['csrf_token'] ?? '');

$albumId = (int) ($_POST['album_id'] ?? 0);

if ($albumId <= 0) {
    http_response_code(400);
    exit;
}

// Verifica faixas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM faixas WHERE album_id = ?");
$stmt->execute([$albumId]);

if ($stmt->fetchColumn() > 0) {
    exit;
}

// Busca capa
$stmt = $pdo->prepare("SELECT capa FROM albuns WHERE id = ?");
$stmt->execute([$albumId]);
$album = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$album) {
    http_response_code(404);
    exit;
}

// Exclui álbum
$stmt = $pdo->prepare("DELETE FROM albuns WHERE id = ?");
$stmt->execute([$albumId]);

// Remove arquivo físico
$caminhoCapa = __DIR__ . '/../uploads/' . $album['capa'];

if (!empty($album['capa']) &&
    $album['capa'] !== 'default.jpg' &&
    file_exists($caminhoCapa)) {

    unlink($caminhoCapa);
}

header("Location: /admin/admin.php");
exit;

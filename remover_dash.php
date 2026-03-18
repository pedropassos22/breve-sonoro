<?php
require "includes/config.php";
require "includes/session.php";

verificarLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Método não permitido.");
}

validarCSRF($_POST['csrf_token'] ?? '');

$album_id = (int) ($_POST['album_id'] ?? 0);

if ($album_id <= 0) {
    exit("ID inválido.");
}

$stmt = $pdo->prepare("
    DELETE FROM usuario_dash
    WHERE usuario_id = ? AND album_id = ?
");

$stmt->execute([$_SESSION['usuario_id'], $album_id]);

header("Location: album.php?id=" . $album_id);
exit();

<?php
require "includes/config.php";
require "includes/session.php";

verificarLogin();

$usuario_id = $_SESSION['usuario_id'];
$album_id = (int) ($_POST['album_id'] ?? 0);
$progresso = (int) ($_POST['progresso'] ?? 0);

if ($album_id <= 0) {
    http_response_code(400);
    echo json_encode(["erro" => "ID de álbum inválido"]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO progresso_album (usuario_id, album_id, progresso)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE progresso = VALUES(progresso)
");

$stmt->execute([$usuario_id, $album_id, $progresso]);

echo json_encode(["status" => "ok"]);

<?php
require "../includes/config.php";
require "../includes/session.php";

verificarAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Método não permitido.");
}

validarCSRF($_POST['csrf_token'] ?? '');

$albumId = (int) ($_POST['album_id'] ?? 0);

if ($albumId <= 0) {
    exit("ID inválido.");
}

// Verifica se o álbum possui faixas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM faixas WHERE album_id = ?");
$stmt->execute([$albumId]);
$totalFaixas = $stmt->fetchColumn();

if ($totalFaixas > 0) {
    exit("Não é possível excluir um álbum que possui faixas.");
}

// Busca caminho da capa
$stmt = $pdo->prepare("SELECT capa FROM albuns WHERE id = ?");
$stmt->execute([$albumId]);
$album = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$album) {
    exit("Álbum não encontrado.");
}

// Exclui do banco
$stmt = $pdo->prepare("DELETE FROM albuns WHERE id = ?");
$stmt->execute([$albumId]);

// Remove arquivo físico se existir
$caminhoCapa = "../uploads/" . $album['capa'];

if (file_exists($caminhoCapa)) {
    unlink($caminhoCapa);
}

header("Location: admin.php");
exit();
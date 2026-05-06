<?php

require __DIR__ . '/../../app/includes/bootstrap.php';

verificarLogin();
verificarAdmin();

validarCSRF($_POST['csrf_token'] ?? null);


$album_id = filter_input(INPUT_POST, 'album_id', FILTER_VALIDATE_INT);

if (!$album_id) {
    exit('Álbum inválido');
}

$links = $_POST['streaming'] ?? [];

salvarLinksStreaming($pdo, $album_id, $links);

header("Location: /album.php?id=" . $album_id);
exit;

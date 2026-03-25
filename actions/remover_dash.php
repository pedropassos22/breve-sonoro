<?php
require "../includes/bootstrap.php";

verificarLogin();
validarPost();
validarCSRF($_POST['csrf_token'] ?? '');



$album_id = (int) ($_POST['album_id'] ?? 0);
$usuario_id = $_SESSION['usuario_id'];

try {
    removerDaDash($pdo, $usuario_id, $album_id);
} catch (Exception $e) {
    exit($e->getMessage());
}

header("Location: ../album.php?id=" . $album_id);
exit();

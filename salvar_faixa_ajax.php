<?php
require "includes/config.php";
require "includes/session.php";

verificarLogin();

$data = json_decode(file_get_contents("php://input"), true);

$id        = $data['id'] ?? null;
$album_id  = $data['album_id'];
$numero    = $data['numero'];
$nome      = $data['nome'];
$duracao   = $data['duracao'];

if ($id) {

    // UPDATE
    $stmt = $pdo->prepare("
        UPDATE faixas
        SET numero = ?, nome = ?, duracao = ?
        WHERE id = ?
    ");

    $stmt->execute([$numero, $nome, $duracao, $id]);

    echo json_encode([
        "status" => "updated",
        "id" => $id
    ]);

} else {

    // INSERT
    $stmt = $pdo->prepare("
        INSERT INTO faixas (album_id, numero, nome, duracao)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$album_id, $numero, $nome, $duracao]);

    $novo_id = $pdo->lastInsertId();

    echo json_encode([
        "status" => "created",
        "id" => $novo_id
    ]);
}

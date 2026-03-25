<?php

function buscarAlbumPorId($pdo, $album_id) {

    $stmt = $pdo->prepare("
        SELECT albuns.*, bandas.nome AS banda_nome
        FROM albuns
        JOIN bandas ON albuns.banda_id = bandas.id
        WHERE albuns.id = ?
    ");

    $stmt->execute([$album_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}



function buscarFaixasDoAlbum($pdo, $album_id, $usuario_id) {

    $stmt = $pdo->prepare("
        SELECT 
            f.id,
            f.album_id,
            f.disco,
            f.numero,
            f.nome,
            f.duracao,

                (
                SELECT COUNT(*)
                FROM reproducoes r
                WHERE r.faixa_id = f.id
                AND r.usuario_id = ?
                ) AS total_ouvidas,

            a.nota,
            a.favorita

        FROM faixas f

        LEFT JOIN avaliacoes a
            ON f.id = a.faixa_id
            AND a.usuario_id = ?

        WHERE f.album_id = ?

        ORDER BY f.disco ASC, f.numero ASC
    ");

    $stmt->execute([$usuario_id, $usuario_id, $album_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



function buscarProgressoAlbum($pdo, $usuario_id, $album_id) {

    $stmt = $pdo->prepare("
        SELECT progresso 
        FROM progresso_album
        WHERE usuario_id = ? AND album_id = ?
    ");

    $stmt->execute([$usuario_id, $album_id]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['progresso'] ?? 0;
}


function registrarReproducao($pdo, $usuario_id, $faixa_id) {

    if ($faixa_id <= 0) {
        throw new Exception("ID da faixa inválido.");
    }

    $stmt = $pdo->prepare("
        INSERT INTO reproducoes (usuario_id, faixa_id)
        VALUES (?, ?)
    ");

    $stmt->execute([$usuario_id, $faixa_id]);
}

function adicionarNaDash($pdo, $usuario_id, $album_id) {

    if ($album_id <= 0) {
        throw new Exception("ID do álbum inválido.");
    }

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO usuario_dash (usuario_id, album_id)
        VALUES (?, ?)
    ");

    $stmt->execute([$usuario_id, $album_id]);
}


function removerDaDash($pdo, $usuario_id, $album_id) {

    if ($album_id <= 0) {
        throw new Exception("ID do álbum inválido.");
    }

    $stmt = $pdo->prepare("
        DELETE FROM usuario_dash
        WHERE usuario_id = ? AND album_id = ?
    ");

    $stmt->execute([$usuario_id, $album_id]);
}

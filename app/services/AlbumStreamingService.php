<?php

function buscarLinksStreaming($pdo, int $albumId)
{
    $stmt = $pdo->prepare("
        SELECT
            p.nome,
            p.slug,
            l.url
        FROM album_streaming_links l
        JOIN plataformas_streaming p
            ON p.id = l.plataforma_id
        WHERE l.album_id = ?
          AND p.ativo = 1
        ORDER BY p.nome
    ");

    $stmt->execute([$albumId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

<?php

function buscarPlataformasStreaming($pdo)
{
    $stmt = $pdo->query("
        SELECT id, nome, slug
        FROM plataformas_streaming
        WHERE ativo = 1
        ORDER BY nome
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



function buscarLinksStreamingIndexados($pdo, int $albumId)
{
    $stmt = $pdo->prepare("
        SELECT plataforma_id, url
        FROM album_streaming_links
        WHERE album_id = ?
    ");

    $stmt->execute([$albumId]);

    $links = [];

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $links[$row['plataforma_id']] = $row['url'];
    }

    return $links;
}



function salvarLinksStreaming($pdo, int $albumId, array $links)
{
    $pdo->prepare("
        DELETE FROM album_streaming_links
        WHERE album_id = ?
    ")->execute([$albumId]);

    $stmt = $pdo->prepare("
        INSERT INTO album_streaming_links
        (album_id, plataforma_id, url)
        VALUES (?, ?, ?)
    ");

    foreach ($links as $plataformaId => $url) {

        $url = trim($url);

        if ($url === '') {
            continue;
        }

        $stmt->execute([
            $albumId,
            $plataformaId,
            $url
        ]);
    }
}

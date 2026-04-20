<?php
declare(strict_types=1);

/**
 * AlbumService
 *
 * Responsável por:
 * - leitura de álbuns
 * - faixas
 * - progresso musical
 * - reproduções
 * - dashboard do usuário
 */



function buscarAlbumPorId(PDO $pdo, int $album_id) {

    $stmt = $pdo->prepare("
        SELECT albuns.*, bandas.nome AS banda_nome
        FROM albuns
        JOIN bandas ON albuns.banda_id = bandas.id
        WHERE albuns.id = ?
    ");

    $stmt->execute([$album_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}



function buscarFaixasDoAlbum(PDO $pdo, int $album_id, int $usuario_id): array {

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



function calcularProgressoAlbum(PDO $pdo, int $usuario_id, int $album_id): int {

    $stmt = $pdo->prepare("
        SELECT COALESCE(
            ROUND(
                (
                    SUM(
                        CASE
                            WHEN EXISTS (
                                SELECT 1
                                FROM reproducoes r
                                WHERE r.faixa_id = f.id
                                AND r.usuario_id = :usuario_reproducao
                                LIMIT 1
                            )
                            THEN 0.5 ELSE 0
                        END
                        +
                        CASE
                            WHEN av.nota > 0 THEN 0.5
                            ELSE 0
                        END
                    )
                    / COUNT(f.id)
                ) * 100
            ),
        0) AS progresso

        FROM faixas f

        LEFT JOIN avaliacoes av
            ON av.faixa_id = f.id
            AND av.usuario_id = :usuario_avaliacao

        WHERE f.album_id = :album
    ");

    $stmt->execute([
        ':usuario_reproducao' => $usuario_id,
        ':usuario_avaliacao'  => $usuario_id,
        ':album' => $album_id
    ]);

    return (int) $stmt->fetchColumn();
}


function registrarReproducao(PDO $pdo, int $usuario_id, int $faixa_id): void {

    if ($faixa_id <= 0) {
        throw new RuntimeException("ID da faixa inválido.");
    }

    // 🔥 evita duplicar infinitamente (regra do sistema)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM reproducoes 
        WHERE usuario_id = ? AND faixa_id = ?
    ");
    $stmt->execute([$usuario_id, $faixa_id]);
    $total = (int) $stmt->fetchColumn();

    // 🔥 sempre registra reprodução (histórico)
    $stmt = $pdo->prepare("
        INSERT INTO reproducoes (usuario_id, faixa_id)
        VALUES (?, ?)
    ");
    $stmt->execute([$usuario_id, $faixa_id]);
}

function adicionarNaDash(PDO $pdo, int $usuario_id, int $album_id): void {

    if ($album_id <= 0) {
        throw new RuntimeException("ID do álbum inválido.");
    }

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO usuario_dash (usuario_id, album_id)
        VALUES (?, ?)
    ");

    $stmt->execute([$usuario_id, $album_id]);
}


function removerDaDash(PDO $pdo, int $usuario_id, int $album_id): int {

    if ($album_id <= 0) {
        throw new RuntimeException("ID do álbum inválido.");
    }

    $stmt = $pdo->prepare("
        DELETE FROM usuario_dash
        WHERE usuario_id = ? AND album_id = ?
    ");

    $stmt->execute([$usuario_id, $album_id]);

    return $stmt->rowCount();
}

function resolverCapaAlbum(array $album): ?string
{
    /*
        prioridade:

        1. capa local (emergência)
        2. cover art archive
        3. fanart online
    */

    // 1️⃣ capa local salva
    if (!empty($album['capa'])) {
        return BASE_URL . "/uploads/capas/" . $album['capa'];
    }

    // 2️⃣ Cover Art Archive (principal)
    if (!empty($album['mbid_release_group'])) {

        $url = obterUrlCapaMusicBrainz($album['mbid_release_group']);

        if ($url) {
            return $url;
        }
    }

    // 3️⃣ Fanart online (fallback)
    $fanarts = buscarCapasFanart(
        $album['artista_nome'],
        $album['titulo']
    );

    if (!empty($fanarts)) {
        return $fanarts[0];
    }

    return null;
}

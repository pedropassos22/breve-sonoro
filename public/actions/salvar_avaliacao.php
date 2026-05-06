<?php
require __DIR__ . '/../../app/includes/bootstrap.php';
header('Content-Type: application/json');


verificarLogin();
validarPost();
validarCSRF($_POST['csrf_token'] ?? '');

$faixa_id = (int) ($_POST['faixa_id'] ?? 0);

if ($faixa_id <= 0) {
    http_response_code(400);
    echo json_encode(["erro" => "Faixa inválida"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];


// Buscar album_id da faixa
$stmt = $pdo->prepare("
    SELECT album_id FROM faixas WHERE id = ?
");
$stmt->execute([$faixa_id]);
$album_id = $stmt->fetchColumn();

if (!$album_id) {
    exit("Álbum não encontrado.");
}


$nota = isset($_POST['nota']) && $_POST['nota'] !== '' 
    ? floatval($_POST['nota']) 
    : null;

// 🔥 0 significa remover avaliação
if ($nota !== null && $nota <= 0) {
    $nota = null;
}


// Favorita só altera se vier no POST
$favorita_enviada = array_key_exists('favorita', $_POST);
$favorita = $favorita_enviada
    ? ($_POST['favorita'] ? 1 : 0)
    : null;


// Se atribuiu nota, garantir pelo menos 1 reprodução
if ($nota !== null && $nota > 0) {

    // Verifica se já existe reprodução desse usuário para essa faixa
    $stmt = $pdo->prepare("
        SELECT id FROM reproducoes
        WHERE usuario_id = ? AND faixa_id = ?
        LIMIT 1
    ");
    $stmt->execute([$usuario_id, $faixa_id]);

    $existe_reproducao = $stmt->fetch();

    // Se não existir, cria 1 reprodução
    if (!$existe_reproducao) {

        $stmt = $pdo->prepare("
            INSERT INTO reproducoes (usuario_id, faixa_id)
            VALUES (?, ?)
        ");

        $stmt->execute([$usuario_id, $faixa_id]);
    }
}


// ==========================
// SALVAR FAVORITO (INDEPENDENTE)
// ==========================

if ($favorita_enviada) {

    $stmt = $pdo->prepare("
        SELECT id FROM avaliacoes
        WHERE usuario_id = ? AND faixa_id = ?
    ");
    $stmt->execute([$usuario_id, $faixa_id]);

    $existe = $stmt->fetchColumn();

    if ($existe) {

        $stmt = $pdo->prepare("
            UPDATE avaliacoes
            SET favorita = ?
            WHERE usuario_id = ? AND faixa_id = ?
        ");

        $stmt->execute([
            $favorita,
            $usuario_id,
            $faixa_id
        ]);

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO avaliacoes
            (usuario_id, faixa_id, nota, favorita)
            VALUES (?, ?, NULL, ?)
        ");

        $stmt->execute([
            $usuario_id,
            $faixa_id,
            $favorita
        ]);
    }
}



// ==========================
// SALVAR NOTA (INDEPENDENTE)
// ==========================

if (array_key_exists('nota', $_POST)) {

    $stmt = $pdo->prepare("
        SELECT id FROM avaliacoes
        WHERE usuario_id = ? AND faixa_id = ?
    ");
    $stmt->execute([$usuario_id, $faixa_id]);

    $existe = $stmt->fetchColumn();

    if ($nota === null) {

        // remove apenas a nota
        if ($existe) {

            $stmt = $pdo->prepare("
                UPDATE avaliacoes
                SET nota = NULL
                WHERE usuario_id = ? AND faixa_id = ?
            ");

            $stmt->execute([
                $usuario_id,
                $faixa_id
            ]);
        }

    } else {

        if ($existe) {

            $stmt = $pdo->prepare("
                UPDATE avaliacoes
                SET nota = ?
                WHERE usuario_id = ? AND faixa_id = ?
            ");

            $stmt->execute([
                $nota,
                $usuario_id,
                $faixa_id
            ]);

        } else {

            $stmt = $pdo->prepare("
                INSERT INTO avaliacoes
                (usuario_id, faixa_id, nota, favorita)
                VALUES (?, ?, ?, 0)
            ");

            $stmt->execute([
                $usuario_id,
                $faixa_id,
                $nota
            ]);
        }
    }
}



// ==========================
// LIMPEZA FINAL (opcional)
// ==========================

$stmt = $pdo->prepare("
    DELETE FROM avaliacoes
    WHERE usuario_id = ?
    AND faixa_id = ?
    AND nota IS NULL
    AND favorita = 0
");

$stmt->execute([$usuario_id, $faixa_id]);



// ==========================
// Retornar JSON para atualização em tempo real
// ==========================

// Buscar total_ouvidas atualizado da faixa
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM reproducoes
    WHERE faixa_id = ? AND usuario_id = ?
");
$stmt->execute([$faixa_id, $usuario_id]);
$total_ouvidas_atualizado = (int)$stmt->fetchColumn();

// Recalcular progresso do álbum
$stmt = $pdo->prepare("
    SELECT 
        f.id,

        EXISTS (
            SELECT 1
            FROM reproducoes r
            WHERE r.faixa_id = f.id
            AND r.usuario_id = ?
            LIMIT 1
        ) AS ouviu,

        a.nota

    FROM faixas f

    LEFT JOIN avaliacoes a
        ON a.faixa_id = f.id
        AND a.usuario_id = ?

    WHERE f.album_id = ?
");


$stmt->execute([$usuario_id, $usuario_id, $album_id]);
$faixas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_faixas = count($faixas);
$faixas_concluidas = 0;
foreach ($faixas as $faixa) {
    if ($faixa['ouviu']) {
        $contribuicao = 0.5;
        if ($faixa['nota'] > 0) $contribuicao += 0.5;
        $faixas_concluidas += $contribuicao;
    }
}
$progresso_percent = $total_faixas > 0 
    ? round(($faixas_concluidas / $total_faixas) * 100)
    : 0;

// Retornar JSON
header('Content-Type: application/json');
echo json_encode([
    "total_ouvidas" => $total_ouvidas_atualizado,
    "nota" => $nota,
    "favorita" => $favorita,
    "progresso_percent" => $progresso_percent
]);
exit;


<?php
require "includes/config.php";
require "includes/session.php";

verificarLogin();

if (!isset($_POST['faixa_id'])) {
    exit("Faixa não especificada.");
}

$usuario_id = $_SESSION['usuario_id'];
$faixa_id = $_POST['faixa_id'];

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

$favorita = isset($_POST['favorita']) ? 1 : 0;

// Se atribuiu nota, garantir pelo menos 1 reprodução
if ($nota !== null) {

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


// Verifica se já existe avaliação
$stmt = $pdo->prepare("
    SELECT id FROM avaliacoes
    WHERE usuario_id = ? AND faixa_id = ?
");
$stmt->execute([$usuario_id, $faixa_id]);

$existe = $stmt->fetch();

if ($existe) {

    $stmt = $pdo->prepare("
        UPDATE avaliacoes
        SET nota = ?, favorita = ?
        WHERE usuario_id = ? AND faixa_id = ?
    ");

    $stmt->execute([$nota, $favorita, $usuario_id, $faixa_id]);

} else {

    $stmt = $pdo->prepare("
        INSERT INTO avaliacoes (usuario_id, faixa_id, nota, favorita)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([$usuario_id, $faixa_id, $nota, $favorita]);
}

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
    SELECT f.id, COUNT(r.id) AS total_ouvidas, a.nota
    FROM faixas f
    LEFT JOIN reproducoes r
        ON f.id = r.faixa_id
        AND r.usuario_id = ?
    LEFT JOIN avaliacoes a
        ON f.id = a.faixa_id
        AND a.usuario_id = ?
    WHERE f.album_id = ?
    GROUP BY f.id
");
$stmt->execute([$usuario_id, $usuario_id, $album_id ?? 0]);
$faixas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_faixas = count($faixas);
$faixas_concluidas = 0;
foreach ($faixas as $faixa) {
    if ($faixa['total_ouvidas'] > 0) {
        $contribuicao = 0.5;
        if ($faixa['nota'] !== null) $contribuicao += 0.5;
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


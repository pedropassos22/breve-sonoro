<?php

require __DIR__ . '/../_init.php';

verificarLogin();
verificarAdmin();

header('Content-Type: application/json');

$mbid = $_GET['mbid'] ?? '';

if (empty($mbid)) {

    echo json_encode([
        'sucesso' => false,
        'erro' => 'MBID não informado'
    ]);

    exit;
}

$faixas = buscarFaixasAlbum($mbid);

echo json_encode([
    'sucesso'      => true,
    'total_faixas' => count($faixas),
    'faixas'       => $faixas
]);
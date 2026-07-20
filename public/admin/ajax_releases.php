<?php

require __DIR__ . '/../_init.php';

verificarLogin();
verificarAdmin();

header('Content-Type: application/json; charset=utf-8');

$mbid = $_GET['mbid'] ?? '';

if (empty($mbid)) {

    echo json_encode([
        'sucesso' => false,
        'erro' => 'MBID não informado'
    ]);

    exit;
}


$resultado = buscarReleasesDoReleaseGroup($mbid);


echo json_encode([
    'sucesso' => true,
    'dados' => $resultado
], JSON_UNESCAPED_UNICODE);
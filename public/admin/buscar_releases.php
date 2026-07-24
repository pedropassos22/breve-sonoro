<?php

require __DIR__ . '/../_init.php';

header('Content-Type: application/json; charset=utf-8');

$mbid = $_GET['mbid'] ?? '';
$ano = $_GET['ano'] ?? null;

if (empty($mbid)) {

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'MBID do Release Group não informado.'
    ]);

    exit;
}

$releases = buscarReleasesDoReleaseGroup($mbid, $ano);

echo json_encode([
    'sucesso' => true,
    'dados' => $releases
], JSON_UNESCAPED_UNICODE);
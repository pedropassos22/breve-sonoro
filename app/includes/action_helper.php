<?php

function validarPost() {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método não permitido.');
    }

    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        http_response_code(403);
        exit('CSRF inválido.');
    }
}


function obterUsuarioId() {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        exit('Usuário não autenticado.');
    }

    return $_SESSION['usuario_id'];
}


function capaUrl(?string $mbid = null, ?string $capa = null): string
{
    /*
    =================================
    1️⃣ COVER ART ARCHIVE (PRIORIDADE)
    =================================
    */

    if (!empty($mbid)) {
        return "https://coverartarchive.org/release-group/{$mbid}/front-500.jpg";
    }

    /*
    =========================
    2️⃣ CAPA EXTERNA MANUAL
    =========================
    */

    if (!empty($capa) && str_starts_with($capa, 'http')) {
        return $capa;
    }

    /*
    =========================
    3️⃣ CAPA LOCAL
    =========================
    */

    if (!empty($capa)) {
        return "/uploads/capas/" . $capa;
    }

    /*
    =========================
    4️⃣ DEFAULT
    =========================
    */

    return "/uploads/capas/default.jpg";
}





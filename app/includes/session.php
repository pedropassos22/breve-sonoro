<?php

// Inicia a sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Gera token CSRF automaticamente
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


function verificarLogin() {

    if (empty($_SESSION['usuario_id'])) {

        // Guarda página que tentou acessar
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

        header("Location: " . BASE_URL . "login.php");

        exit();
    }
}

function verificarAdmin() {

    verificarLogin(); // garante que está logado primeiro

    if (empty($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {

        http_response_code(403);
        exit("Acesso negado.");
    }
}

function validarCSRF($token) {

    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit("Requisição inválida.");
    }
}



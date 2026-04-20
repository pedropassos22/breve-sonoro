<?php

// ============================
// GERAR TOKEN CSRF
// ============================
function gerarCSRFToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}


// ============================
// INPUT HTML AUTOMÁTICO
// ============================
function csrfField(): string
{
    $token = gerarCSRFToken();

    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars($token, ENT_QUOTES, 'UTF-8') .
        '">';
}


// ============================
// VALIDAR TOKEN
// ============================
function validarCSRF(?string $token): void
{
    if (
        empty($_SESSION['csrf_token']) ||
        empty($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        exit('Token CSRF inválido.');
    }
}

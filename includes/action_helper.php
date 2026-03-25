<?php

function validarPost() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit("Método não permitido.");
    }
}

function obterUsuarioId() {
    return $_SESSION['usuario_id'] ?? null;
}

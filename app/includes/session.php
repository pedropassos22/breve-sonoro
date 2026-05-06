<?php

/**
 * ======================================
 * SESSION & AUTH CONTROL
 * ======================================
 */

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']), // só HTTPS em produção
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();

    /*
    |--------------------------------------------------------------------------
    | SESSION TIMEOUT CONTROL
    |--------------------------------------------------------------------------
    */

    if (isset($_SESSION['usuario_id'])) {

        // Se NÃO marcou lembrar
        if (empty($_SESSION['lembrar'])) {

            $tempo_limite = 1800; // 30 Minutos

            if (isset($_SESSION['ultima_atividade'])) {

                if (time() - $_SESSION['ultima_atividade'] > $tempo_limite) {

                    session_unset();
                    session_destroy();

                    header("Location: /login.php");
                    exit();
                }
            }

            $_SESSION['ultima_atividade'] = time();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REMEMBER ME AUTO LOGIN
    |--------------------------------------------------------------------------
    */

    if (
        empty($_SESSION['usuario_id']) &&
        !empty($_COOKIE['remember_token'])
    ) {

        global $pdo;

        $stmt = $pdo->prepare("
            SELECT id, nome, tipo
            FROM usuarios
            WHERE remember_token = ?
            LIMIT 1
        ");

        $stmt->execute([$_COOKIE['remember_token']]);
        $usuario = $stmt->fetch();

        if ($usuario) {

            session_regenerate_id(true);

            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            $_SESSION['lembrar'] = true;
            $_SESSION['ultima_atividade'] = time();
        }
    }


}


/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

function verificarLogin()
{
    if (empty($_SESSION['usuario_id'])) {

        $_SESSION['redirect_after_login'] =
            $_SERVER['REQUEST_URI'] ?? '/';

        header("Location: /login.php");
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| ADMIN CHECK
|--------------------------------------------------------------------------
*/

function verificarAdmin()
{
    verificarLogin();

    if (
        empty($_SESSION['usuario_tipo']) ||
        $_SESSION['usuario_tipo'] !== 'admin'
    ) {
        http_response_code(403);
        exit('Acesso negado.');
    }
}

/*
|--------------------------------------------------------------------------
| SESSION REGENERATION
|--------------------------------------------------------------------------
*/

function regenerarSessao()
{
    session_regenerate_id(true);
}

/*
|--------------------------------------------------------------------------
| AUTH HELPERS
|--------------------------------------------------------------------------
*/

function usuarioLogado(): bool
{
    return !empty($_SESSION['usuario_id']);
}

function usuarioId(): ?int
{
    return $_SESSION['usuario_id'] ?? null;
}

function usuarioNome(): ?string
{
    return $_SESSION['usuario_nome'] ?? null;
}

function usuarioTipo(): ?string
{
    return $_SESSION['usuario_tipo'] ?? null;
}

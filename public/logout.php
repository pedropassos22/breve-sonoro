<?php
require __DIR__ . '/../app/includes/bootstrap.php';


if (isset($_SESSION['usuario_id'])) {

    $stmt = $pdo->prepare("
        UPDATE usuarios
        SET remember_token = NULL
        WHERE id = ?
    ");

    $stmt->execute([$_SESSION['usuario_id']]);
}

setcookie('remember_token', '', time() - 3600, '/');


// Limpa sessão
$_SESSION = [];

// Remove cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroi sessão
session_destroy();

// Redireciona
header("Location: /login.php");
exit;

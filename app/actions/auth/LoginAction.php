<?php

require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../Services/AuthService.php';

validarPost();
validarCSRF($_POST['csrf_token'] ?? '');

$service = new AuthService($pdo);

$email = trim($_POST['email']);
$senha = $_POST['senha'];

$usuario = $service->login($email, $senha);

if (!$usuario) {
    $_SESSION['erro'] = "Email ou senha inválidos.";
    header("Location: /login.php");
    exit;
}

regenerarSessao();

$_SESSION['usuario_id']   = $usuario['id'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_tipo'] = $usuario['tipo'];

header("Location: /dash.php");
exit;
<?php

require __DIR__ . '/../../includes/bootstrap.php';
require __DIR__ . '/../../Services/AuthService.php';

validarPost();
validarCSRF($_POST['csrf_token'] ?? '');

$service = new AuthService($pdo);

$nome  = trim($_POST['nome']);
$email = trim($_POST['email']);
$senha = $_POST['senha'];

$ok = $service->registrar($nome, $email, $senha);

if (!$ok) {
    $_SESSION['erro'] = "Email já cadastrado.";
} else {
    $_SESSION['erro'] = "Usuário criado! Faça login.";
}

header("Location: /breve-sonoro/public/login.php");
exit;
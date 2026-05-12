<?php
require __DIR__ . "/../app/includes/bootstrap.php";

$erro = $_SESSION['erro'] ?? '';
unset($_SESSION['erro']);



// Se já estiver logado, redireciona conforme tipo
if (usuarioLogado()) {

    if (usuarioTipo() === 'admin') {
        header("Location: admin/admin.php");
    } else {
        header("Location: dash.php");
    }

    exit;
}


?>



<!DOCTYPE html>
<html>
<head>
    <title>Login - breve-sonoro</title>
</head>
<body>

<h2>Login</h2>

<?php if ($erro): ?>
    <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>


<h3>Entrar</h3>

<form method="POST" action="/actions/auth/login.php">
    <?= csrfField() ?>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Senha:</label><br>
    <input type="password" name="senha" required><br><br>

    <label>
    <input type="checkbox" name="lembrar">
    Lembrar de mim neste navegador
    </label>
    <br><br>

    <button type="submit">Entrar</button>
</form>

<hr>

<h3>Cadastrar</h3>
<form method="POST" action="/actions/auth/register.php">
    <?= csrfField() ?>

    <label>Nome:</label><br>
    <input type="text" name="nome" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Senha:</label><br>
    <input type="password" name="senha" required><br><br>

    <button type="submit">Cadastrar</button>
</form>


</body>
</html>

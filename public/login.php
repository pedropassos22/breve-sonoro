<?php
require __DIR__ . "/../app/includes/bootstrap.php";



// Se já estiver logado, redireciona conforme tipo
if (usuarioLogado()) {

    if (usuarioTipo() === 'admin') {
        header("Location: admin/admin.php");
    } else {
        header("Location: dash.php");
    }

    exit;
}


$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    validarPost();
    validarCSRF($_POST['csrf_token'] ?? '');


    $acao = $_POST['acao'];

    // ==========================
    // LOGIN
    // ==========================
    if ($acao === "login") {

        $email = trim($_POST['email']);
        $senha = $_POST['senha'];


        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {

            regenerarSessao();

            $lembrar = isset($_POST['lembrar']);

            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            $_SESSION['ultima_atividade'] = time();
            $_SESSION['lembrar'] = $lembrar;

            if ($lembrar) {

                $token = bin2hex(random_bytes(32));

                setcookie(
                    'remember_token',
                    $token,
                    time() + (86400 * 30),
                    '/',
                    '',
                    isset($_SERVER['HTTPS']),
                    true
                );

                $stmt = $pdo->prepare("
                    UPDATE usuarios
                    SET remember_token = ?
                    WHERE id = ?
                ");

                $stmt->execute([$token, $usuario['id']]);
            }

            if ($usuario['tipo'] === 'admin') {
                header("Location: admin/admin.php");
            } else {
                header("Location: dash.php");
            }

            exit();

        } else {
            $erro = "Email ou senha inválidos.";
        }
    }

    // ==========================
    // CADASTRO
    // ==========================
    if ($acao === "cadastro") {

        $nome  = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha = $_POST['senha'];


        // Verifica se email já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erro = "Este email já está cadastrado.";
        } else {

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO usuarios (nome, email, senha, tipo)
                VALUES (?, ?, ?, 'comum')
            ");
            $stmt->execute([$nome, $email, $senhaHash]);

            $erro = "Usuário cadastrado com sucesso! Faça login.";
        }
    }
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

<form method="POST">
    <?= csrfField() ?>

    <input type="hidden" name="acao" value="login">

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
<!--
<h3>Cadastrar</h3>
<form method="POST">
    <input type="hidden" name="acao" value="cadastro">

    <label>Nome:</label><br>
    <input type="text" name="nome" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Senha:</label><br>
    <input type="password" name="senha" required><br><br>

    <button type="submit">Cadastrar</button>
</form>
-->

</body>
</html>

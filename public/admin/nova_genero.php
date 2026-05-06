<?php
require dirname(__DIR__, 2) . '/app/includes/bootstrap.php';

verificarLogin();
verificarAdmin();

$mensagem = "";

// Função para gerar slug
function gerarSlug($texto) {

    $texto = strtolower($texto);
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    $texto = trim($texto, '-');

    return $texto;
}

if (isset($_POST['salvar'])) {

    $nome = trim($_POST['nome']);

    if (empty($nome)) {
        $mensagem = "O nome do gênero é obrigatório.";
    } else {

        $slug = gerarSlug($nome);

        // Verifica se já existe
        $stmt = $pdo->prepare("SELECT id FROM generos WHERE nome = ?");
        $stmt->execute([$nome]);

        if ($stmt->rowCount() > 0) {
            $mensagem = "Esse gênero já está cadastrado.";
        } else {

            $stmt = $pdo->prepare("
                INSERT INTO generos (nome, slug, ativo)
                VALUES (?, ?, 1)
            ");
            $stmt->execute([$nome, $slug]);

            $mensagem = "Gênero cadastrado com sucesso!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Novo Gênero - breve-sonoro</title>
    <link rel="stylesheet" href="assets/css/layout.css">
</head>

<body>
    <div class="container-cru">
        <h2>Cadastrar Gênero</h2>

        <?php if ($mensagem): ?>

            <?php if (strpos($mensagem, "sucesso") !== false): ?>
                <p style="color:green;"><?php echo $mensagem; ?></p>
            <?php else: ?>
                <p style="color:red;"><?php echo $mensagem; ?></p>
            <?php endif; ?>

        <?php endif; ?>

        <form method="POST">

            <label>Nome do Gênero:</label><br>
            <input type="text" id="nomeGenero" name="nome" required style="width:300px;"><br><br>
            <button type="submit" name="salvar">Salvar Gênero</button>

        </form>

        <br>
        <a href="admin/admin.php" class="dash-btn">
            Voltar
        </a>


        <?php if (strpos($mensagem, "sucesso") !== false): ?>
        <script>
        window.onload = function() {
            let campo = document.getElementById("nomeGenero");
            campo.value = "";
            campo.focus();
        }
        </script>
        <?php endif; ?>
    <div>

</body>
</html>

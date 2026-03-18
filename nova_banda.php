<?php
require "includes/config.php";
require "includes/session.php";

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

function normalizarNome($texto) {

    $texto = strtolower($texto);
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
    $texto = preg_replace('/[^a-z0-9]/', '', $texto);

    return $texto;
}

if (isset($_POST['salvar'])) {

    $nome = trim($_POST['nome']);

    $ano_formacao = !empty($_POST['ano_formacao']) ? $_POST['ano_formacao'] : null;
    $cidade = !empty($_POST['cidade']) ? trim($_POST['cidade']) : null;


    if (empty($nome)) {
        $mensagem = "O nome da banda é obrigatório.";
    } else {

        $slug = gerarSlug($nome);
        $nome_normalizado = normalizarNome($nome);

        // Verifica se já existe
        $stmt = $pdo->prepare("
            SELECT id FROM bandas 
            WHERE nome_normalizado = ?
        ");
        $stmt->execute([$nome_normalizado]);

        if ($stmt->rowCount() > 0) {
            $mensagem = "Essa banda já está cadastrada.";
        } else {

        $stmt = $pdo->prepare("
            INSERT INTO bandas (nome, slug, nome_normalizado, ano_formacao, cidade)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nome,
            $slug,
            $nome_normalizado,
            $ano_formacao,
            $cidade
        ]);

        $banda_id = $pdo->lastInsertId();

        if (!empty($_POST['generos'])) {
            foreach ($_POST['generos'] as $genero_id) {
                $stmtGenero = $pdo->prepare("
                    INSERT INTO banda_genero (banda_id, genero_id)
                    VALUES (?, ?)
                ");
                $stmtGenero->execute([$banda_id, $genero_id]);
            }
        }


            $mensagem = "Banda cadastrada com sucesso!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Nova Banda - breve-sonoro</title>
    <link rel="stylesheet" href="assets/css/layout.css">
</head>
<body>
    <div class="container-cru">
        <h2>Cadastrar Banda</h2>

        <?php if ($mensagem): ?>

            <?php if (strpos($mensagem, "sucesso") !== false): ?>
                <p style="color:green;"><?php echo $mensagem; ?></p>
            <?php else: ?>
                <p style="color:red;"><?php echo $mensagem; ?></p>
            <?php endif; ?>

        <?php endif; ?>

        <form method="POST">

            <label>Nome da Banda:</label><br>
            <input type="text" name="nome" required style="width:300px;"><br><br>

            <label>Ano de Formação:</label><br>
            <input type="number" name="ano_formacao" min="1900" max="<?php echo date('Y'); ?>" style="width:150px;"><br><br>

            <label>Cidade:</label><br>
            <input type="text" name="cidade" style="width:300px;"><br><br>

            <label>Gêneros:</label><br>
            <select name="generos[]" multiple required style="width:300px; height:120px;">
                <?php
                $stmt = $pdo->query("SELECT id, nome FROM generos WHERE ativo = 1 ORDER BY nome");
                while ($genero = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$genero['id']}'>{$genero['nome']}</option>";
                }
                ?>
            </select><br><br>


            <button type="submit" name="salvar">Salvar Banda</button>

        </form>

        <br>
        <a href="admin/admin.php" class="dash-btn">
            Voltar
        </a>
    </div>

</body>
</html>

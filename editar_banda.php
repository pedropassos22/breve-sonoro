<?php
require "includes/config.php";
require "includes/session.php";

verificarLogin();
verificarAdmin();

$mensagem = "";

if (!isset($_GET['id'])) {
    die("Banda não especificada.");
}

$banda_id = intval($_GET['id']);


// Funções
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


// BUSCAR DADOS DA BANDA
$stmt = $pdo->prepare("SELECT * FROM bandas WHERE id = ?");
$stmt->execute([$banda_id]);
$banda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$banda) {
    die("Banda não encontrada.");
}


// BUSCAR GÊNEROS DA BANDA
$stmt = $pdo->prepare("SELECT genero_id FROM banda_genero WHERE banda_id = ?");
$stmt->execute([$banda_id]);
$generos_banda = $stmt->fetchAll(PDO::FETCH_COLUMN);


// SALVAR ALTERAÇÃO
if (isset($_POST['salvar'])) {

    $nome = trim($_POST['nome']);
    $ano_formacao = !empty($_POST['ano_formacao']) ? $_POST['ano_formacao'] : null;
    $cidade = !empty($_POST['cidade']) ? trim($_POST['cidade']) : null;

    if (empty($nome)) {

        $mensagem = "O nome da banda é obrigatório.";

    } else {

        $slug = gerarSlug($nome);
        $nome_normalizado = normalizarNome($nome);

        // verificar duplicação
        $stmt = $pdo->prepare("
            SELECT id FROM bandas
            WHERE nome_normalizado = ?
            AND id != ?
        ");
        $stmt->execute([$nome_normalizado, $banda_id]);

        if ($stmt->rowCount() > 0) {

            $mensagem = "Já existe outra banda com esse nome.";

        } else {

            // atualizar banda
            $stmt = $pdo->prepare("
                UPDATE bandas
                SET nome = ?, slug = ?, nome_normalizado = ?, ano_formacao = ?, cidade = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $nome,
                $slug,
                $nome_normalizado,
                $ano_formacao,
                $cidade,
                $banda_id
            ]);


            // limpar generos antigos
            $pdo->prepare("DELETE FROM banda_genero WHERE banda_id = ?")
                ->execute([$banda_id]);

            // salvar generos novos
            if (!empty($_POST['generos'])) {

                foreach ($_POST['generos'] as $genero_id) {

                    $stmtGenero = $pdo->prepare("
                        INSERT INTO banda_genero (banda_id, genero_id)
                        VALUES (?, ?)
                    ");

                    $stmtGenero->execute([$banda_id, $genero_id]);
                }
            }

            $mensagem = "Banda atualizada com sucesso!";

            // atualizar dados na tela
            $banda['nome'] = $nome;
            $banda['ano_formacao'] = $ano_formacao;
            $banda['cidade'] = $cidade;

            $generos_banda = $_POST['generos'] ?? [];
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Editar Banda - breve-sonoro</title>
<link rel="stylesheet" href="assets/css/layout.css">
</head>

<body>

<div class="container-cru">

<h2>Editar Banda</h2>

<?php if ($mensagem): ?>

<?php if (strpos($mensagem, "sucesso") !== false): ?>
<p style="color:green;"><?php echo $mensagem; ?></p>
<?php else: ?>
<p style="color:red;"><?php echo $mensagem; ?></p>
<?php endif; ?>

<?php endif; ?>


<form method="POST">

<label>Nome da Banda:</label><br>

<input
type="text"
name="nome"
value="<?php echo htmlspecialchars($banda['nome']); ?>"
required
style="width:300px;"
><br><br>


<label>Ano de Formação:</label><br>

<input
type="number"
name="ano_formacao"
value="<?php echo htmlspecialchars($banda['ano_formacao']); ?>"
min="1900"
max="<?php echo date('Y'); ?>"
style="width:150px;"
><br><br>


<label>Cidade:</label><br>

<input
type="text"
name="cidade"
value="<?php echo htmlspecialchars($banda['cidade']); ?>"
style="width:300px;"
><br><br>


<label>Gêneros:</label><br>

<select name="generos[]" multiple required style="width:300px;height:120px;">

<?php

$stmt = $pdo->query("
SELECT id, nome
FROM generos
WHERE ativo = 1
ORDER BY nome
");

while ($genero = $stmt->fetch(PDO::FETCH_ASSOC)) {

$selected = in_array($genero['id'], $generos_banda) ? "selected" : "";

echo "<option value='{$genero['id']}' $selected>{$genero['nome']}</option>";
}

?>

</select>

<br><br>

<button type="submit" name="salvar">Salvar Alterações</button>

</form>

<br>

<a href="admin/admin.php" class="dash-btn">
Voltar
</a>

</div>

</body>
</html>
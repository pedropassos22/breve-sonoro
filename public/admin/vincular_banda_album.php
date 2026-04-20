<?php
require "includes/config.php";
require "includes/session.php";

verificarLogin();
verificarAdmin();

if (!isset($_GET['id'])) {
    die("Álbum não informado.");
}

$album_id = $_GET['id'];

// Buscar álbum
$stmt = $pdo->prepare("SELECT * FROM albuns WHERE id = ?");
$stmt->execute([$album_id]);
$album = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$album) {
    die("Álbum não encontrado.");
}

// Atualizar banda
if (isset($_POST['salvar'])) {

    $banda_id = $_POST['banda_id'];

    $stmt = $pdo->prepare("UPDATE albuns SET banda_id = ? WHERE id = ?");
    $stmt->execute([$banda_id, $album_id]);

    echo "<p style='color:green;'>Banda vinculada com sucesso!</p>";
}
?>

<h2>Vincular Banda ao Álbum</h2>

<p><strong><?php echo htmlspecialchars($album['titulo']); ?></strong></p>

<form method="POST">

<select name="banda_id" required>
    <option value="">Selecione a banda</option>
    <?php
    $stmtBandas = $pdo->query("SELECT id, nome FROM bandas ORDER BY nome ASC");
    while ($banda = $stmtBandas->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='{$banda['id']}'>{$banda['nome']}</option>";
    }
    ?>
</select>

<br><br>
<button type="submit" name="salvar">Salvar</button>

</form>

<br>
<a href="index.php">Voltar</a>

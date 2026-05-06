<?php

require __DIR__ . '/../../app/includes/bootstrap.php';

verificarLogin();
verificarAdmin();

require BASE_PATH . '/app/includes/musicbrainz.php';

$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['banda'])) {

    $banda = trim($_GET['banda']);

    if ($banda !== '') {
        $resultado = buscarBandaMusicBrainz($banda);
    }
}
?>

<h2>Buscar Banda no MusicBrainz</h2>

<form method="GET">

Nome da banda:
<input type="text" name="banda">

<button type="submit">Buscar</button>

</form>

<hr>

<?php if ($resultado): ?>

<h3><?php echo htmlspecialchars($resultado['nome']); ?></h3>

Ano de formação:
<?php
echo htmlspecialchars($resultado['ano_formacao'] ?? "Não informado");
?>

<br><br>

Cidade / País:
<?php
echo htmlspecialchars($resultado['cidade'] ?? "Não informado");
?>

<br><br>

Gêneros (tags):

<ul>

<?php
if (!empty($resultado['generos'])) {

    foreach ($resultado['generos'] as $genero) {

        echo "<li>" . htmlspecialchars($genero) . "</li>";

    }

}
?>

</ul>

<?php endif; ?>
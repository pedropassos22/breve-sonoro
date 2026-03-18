<?php

require "includes/musicbrainz.php";

$resultado = null;

if (!empty($_GET['banda'])) {

    $banda = $_GET['banda'];

    $resultado = buscarBandaMusicBrainz($banda);

}

?>

<h2>Buscar Banda no MusicBrainz</h2>

<form>

Nome da banda:
<input type="text" name="banda">

<button type="submit">Buscar</button>

</form>

<hr>

<?php if ($resultado): ?>

<h3><?php echo htmlspecialchars($resultado['nome']); ?></h3>

Ano de formação:
<?php
echo $resultado['ano_formacao'] ?? "Não informado";
?>

<br><br>

Cidade / País:
<?php
echo $resultado['cidade'] ?? "Não informado";
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
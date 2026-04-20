<?php

require "includes/config.php";
require "includes/musicbrainz.php";

$faixas = [];

if (!empty($_GET['mbid'])) {

    $mbid = $_GET['mbid'];

    $faixas = buscarFaixasAlbum($mbid);

}

?>

<h2>Testar importação de faixas (com discos)</h2>

<form>

MBID do álbum:
<input type="text" name="mbid" style="width:400px">

<button type="submit">Testar</button>

</form>

<hr>

<?php if (!empty($faixas)): ?>

<?php
// separar faixas por disco
$discos = [];
foreach ($faixas as $f) {
    $disco = $f['disco'] ?? 1;
    if (!isset($discos[$disco])) {
        $discos[$disco] = [];
    }
    $discos[$disco][] = $f;
}
?>

<?php foreach ($discos as $discoNum => $faixasDoDisco): ?>

<h3>Disco <?php echo $discoNum; ?></h3>

<table border="1" cellpadding="5">
<tr>
<th>Número</th>
<th>Título</th>
<th>Duração</th>
</tr>

<?php foreach ($faixasDoDisco as $f): ?>
<tr>
<td><?php echo $f['numero'] ?? '-'; ?></td>
<td><?php echo htmlspecialchars($f['titulo']); ?></td>
<td><?php echo $f['duracao'] ?? '-'; ?></td>
</tr>
<?php endforeach; ?>

</table>
<br>

<?php endforeach; ?>

<p><strong>Total de faixas:</strong> <?php echo count($faixas); ?></p>

<?php elseif(isset($_GET['mbid'])): ?>

<p>Nenhuma faixa encontrada.</p>

<?php endif; ?>

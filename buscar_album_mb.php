<?php

require "includes/config.php";
require "includes/musicbrainz.php";

$resultados = [];

if (!empty($_GET['album'])) {

    $album = $_GET['album'];
    $artista = $_GET['artista'] ?? "";

    $resultados = buscarAlbumMusicBrainz($album, $artista);

}

?>

<h2>Buscar álbum no MusicBrainz</h2>

<form>

Álbum:
<input type="text" name="album">

Artista:
<input type="text" name="artista">

<button type="submit">Buscar</button>

</form>

<hr>

<?php foreach ($resultados as $r): ?>

<div style="margin-bottom:20px;">

<?php

$titulo = $r['titulo'] ?? 'Sem título';
$artista_nome = $r['artista'] ?? 'Desconhecido';
$ano = $r['ano'] ?? '';
$mbid = $r['mbid'] ?? '';

?>

<strong><?php echo htmlspecialchars($titulo); ?></strong><br>

Artista:
<?php echo htmlspecialchars($artista_nome); ?><br>

Ano:
<?php echo $ano; ?><br><br>

MBID:
<?php echo $mbid; ?><br><br>

<a href="teste_faixas.php?mbid=<?php echo $mbid; ?>">
Testar faixas
</a>

<br><br>

<a href="novo_album.php?
titulo=<?php echo urlencode($titulo); ?>
&banda_nome=<?php echo urlencode($artista_nome); ?>
&ano=<?php echo $ano; ?>
&mbid=<?php echo $mbid; ?>
">


Importar este álbum

</a>

</div>


<?php endforeach; ?>

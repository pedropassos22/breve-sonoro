<?php

require __DIR__ . '/../_init.php';


verificarLogin();
verificarAdmin();



$resultados = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['album'])) {

    $album = trim($_GET['album']);
    $artista = trim($_GET['artista'] ?? '');

    if ($album !== '') {
        $resultados = buscarAlbumMusicBrainz($album, $artista);
    }
}
?>


<h2>Buscar álbum no MusicBrainz</h2>

<form method="GET">

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
$mbid = htmlspecialchars($r['mbid'] ?? '');

?>

<strong><?php echo htmlspecialchars($titulo); ?></strong><br>

Artista:
<?php echo htmlspecialchars($artista_nome); ?><br>

Ano:
<?php echo $ano; ?><br><br>

MBID:
<?php echo $mbid; ?><br><br>



<br><br>

<a href="novo_album.php?titulo=<?php echo urlencode($titulo); ?>&banda_nome=<?php echo urlencode($artista_nome); ?>&ano=<?php echo urlencode($ano); ?>&mbid=<?php echo urlencode($mbid); ?>">



Importar este álbum

</a>

</div>


<?php endforeach; ?>

<?php

require "includes/musicbrainz.php";

$mbid = $_GET['mbid'] ?? null;

if (!$mbid) {
    die("MBID não informado");
}

$faixas = buscarFaixasAlbum($mbid);

echo "<pre>";
print_r($faixas);

    <?php

    function musicBrainzRequest($url, $sleep = true) {

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: breve-sonoro/1.0"
        ]
    ];

    $context = stream_context_create($opts);

    if ($sleep) {
        sleep(1);
    }

$json = file_get_contents($url, false, $context);

if ($json === false) {

    echo '<pre>';
    echo "URL:\n";
    echo $url;
    echo "\n\nERRO:\n";
    print_r(error_get_last());
    echo '</pre>';

    exit;
}

    $dados = json_decode($json, true);

    if (!is_array($dados)) {
        return null;
    }

    return $dados;
}

    function buscarAlbumMusicBrainz($album, $artista = "") {

        $query = 'release-group:"' . $album . '"';


        if (!empty($artista)) {
            $query .= " AND artist:$artista";
        }

        $query = urlencode($query);

        $url = "https://musicbrainz.org/ws/2/release-group/?query=$query&fmt=json&limit=10";

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: breve-sonoro/1.0"
            ]
        ];

        $context = stream_context_create($opts);

        $resposta = @file_get_contents($url, false, $context);

        if (!$resposta) {
            return [];
        }

        $dados = json_decode($resposta, true);

        if (!isset($dados['release-groups'])) {
            return [];
        }

    $resultados = [];

    foreach ($dados['release-groups'] as $release) {

        // aceitar apenas álbuns
        if (($release['primary-type'] ?? "") !== "Album") {
            continue;
        }

        $ano = null;

        if (!empty($release['first-release-date'])) {
            $ano = substr($release['first-release-date'], 0, 4);
        }

        $artistaNome = null;

        if (!empty($release['artist-credit'][0]['name'])) {
            $artistaNome = $release['artist-credit'][0]['name'];
        }

        $resultados[] = [
            "mbid" => $release['id'],
            "titulo" => $release['title'],
            "artista" => $artistaNome,
            "ano" => $ano
        ];

    }


    return $resultados;

    }




    function buscarFaixasAlbum($mbid) {

        if (!preg_match('/^[a-f0-9\-]{36}$/i', $mbid)) {
            return [];
        }


        $releaseMBID = buscarReleaseDoReleaseGroup($mbid);

        if (!$releaseMBID) {
            return [];
        }

        $url = "https://musicbrainz.org/ws/2/release/$releaseMBID?inc=recordings&fmt=json";


        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: breve-sonoro/1.0"
            ]
        ];

        $context = stream_context_create($opts);

        sleep(1);

        $resposta = @file_get_contents($url, false, $context);


        if (!$resposta) {
            return [];
        }

        $dados = json_decode($resposta, true);

        if (!isset($dados['media'])) {
            return [];
        }

        $faixas = [];
        $titulosUsados = [];


            $discoNumero = 1;

            foreach ($dados['media'] as $media) {

            // ignorar mídias que não são áudio
            if (isset($media['format'])) {

                $formato = strtolower($media['format']);

                if (
                    strpos($formato, 'dvd') !== false ||
                    strpos($formato, 'blu-ray') !== false ||
                    strpos($formato, 'bluray') !== false ||
                    strpos($formato, 'video') !== false
                ) {
                    continue;
                }

            }

            if (!isset($media['tracks'])) {
                continue;
            }

            foreach ($media['tracks'] as $track) {

                $numeroFaixa = null;

            if (!empty($track['position'])) {

                if (is_numeric($track['position'])) {
                    $numeroFaixa = (int)$track['position'];
                }

            }



                if (empty($track['title'])) {
                    continue;
                }

                $titulo = trim($track['title']);
                $titulo = preg_replace('/\s+/', ' ', $titulo);

                $tituloLower = strtolower($titulo);

                /* chave única: disco + título */
                $chave = $discoNumero . '|' . $tituloLower;

                if (isset($titulosUsados[$chave])) {
                    continue;
                }

                $titulosUsados[$chave] = true;


                $duracao = null;

                if (!empty($track['length'])) {

                    $segundos = floor($track['length'] / 1000);

                    $min = floor($segundos / 60);
                    $sec = $segundos % 60;

                    $duracao = sprintf("%02d:%02d", $min, $sec);

                }

                $faixas[] = [
                    'disco' => $discoNumero,
                    'numero' => $numeroFaixa,
                    'titulo' => $titulo,
                    'duracao' => $duracao
                ];


            }

            $discoNumero++;

        }

        // 🚨 Validação de quantidade
        $total = count($faixas);

        if ($total < 3 || $total > 80) {
            return [];
        }

        // reorganizar numeração
        $resultado = [];

        $contadorPorDisco = [];

        foreach ($faixas as $faixa) {

            $disco = $faixa['disco'];

            if (!isset($contadorPorDisco[$disco])) {
                $contadorPorDisco[$disco] = 1;
            }

            $numeroFinal = $faixa['numero'];

            if ($numeroFinal === null || $numeroFinal <= 0) {
                $numeroFinal = $contadorPorDisco[$disco];
            }


            $resultado[] = [
                'disco' => $disco,
                'numero' => $numeroFinal,
                'titulo' => $faixa['titulo'],
                'duracao' => $faixa['duracao']
            ];

            $contadorPorDisco[$disco]++;

        }


        return $resultado;

    }

    function salvarFaixasAlbum($pdo, $album_id, $faixas) {

        $sql = "INSERT INTO faixas (album_id, disco, numero, nome, duracao)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        foreach ($faixas as $faixa) {

            $stmt->execute([
                $album_id,
                $faixa['disco'],
                $faixa['numero'],
                $faixa['titulo'],
                $faixa['duracao']
            ]);

        }

    }




    function baixarCapaMusicBrainz($mbid, $titulo, $bandaNome) {

        $releaseMBID = buscarReleaseDoReleaseGroup($mbid);

        if (!$releaseMBID) {
            return null;
        }

        $url = "https://coverartarchive.org/release/$releaseMBID/front-500";


        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: breve-sonoro/1.0"
            ]
        ];

        $context = stream_context_create($opts);

        $imagem = @file_get_contents($url, false, $context);

        if (!$imagem) {
            return null;
        }

        // gerar nome igual ao seu sistema atual
        $nomeBase = $titulo . $bandaNome;

        $nomeBase = str_replace(' ', '', $nomeBase);
        $nomeBase = iconv('UTF-8', 'ASCII//TRANSLIT', $nomeBase);
        $nomeBase = preg_replace('/[^A-Za-z0-9]/', '', $nomeBase);
        $nomeBase = strtolower($nomeBase);

        $capa_nome = $nomeBase . ".webp";

        $destino = __DIR__ . "/../uploads/capas/" . $capa_nome;

        // se já existir não baixa novamente
        if (file_exists($destino)) {
            return $capa_nome;
        }

        $imagemOriginal = imagecreatefromstring($imagem);

        if (!$imagemOriginal) {
            return null;
        }

        $novoTamanho = 500;

        $imagemRedimensionada = imagecreatetruecolor($novoTamanho, $novoTamanho);

        $largura = imagesx($imagemOriginal);
        $altura  = imagesy($imagemOriginal);

        imagecopyresampled(
            $imagemRedimensionada,
            $imagemOriginal,
            0,0,0,0,
            $novoTamanho,$novoTamanho,
            $largura,$altura
        );

        imagewebp($imagemRedimensionada, $destino, 80);

        imagedestroy($imagemOriginal);
        imagedestroy($imagemRedimensionada);

        return $capa_nome;
    }

    function buscarBandaMusicBrainz($nome) {

        $nome = urlencode($nome);

        $url = "https://musicbrainz.org/ws/2/artist/?query=artist:$nome&fmt=json&limit=1&inc=tags";

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: breve-sonoro/1.0"
            ]
        ];

        $context = stream_context_create($opts);

        $json = @file_get_contents($url, false, $context);

        if (!$json) {
            return null;
        }

        $data = json_decode($json, true);

        if (empty($data['artists'][0])) {
            return null;
        }

        $artist = $data['artists'][0];

        $ano = null;

        if (!empty($artist['life-span']['begin'])) {
            $ano = substr($artist['life-span']['begin'], 0, 4);
        }

        $cidade = null;

        if (!empty($artist['begin-area']['name'])) {

            $cidade = $artist['begin-area']['name'];

            if (!empty($artist['area']['name'])) {
                $cidade .= ", " . $artist['area']['name'];
            }
        }

        $generos = [];

        if (!empty($artist['tags'])) {

            usort($artist['tags'], function($a, $b) {
                return ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
            });

            foreach (array_slice($artist['tags'], 0, 5) as $tag) {
                $generos[] = $tag['name'];
            }
        }

        return [
            "nome" => $artist['name'],
            "ano_formacao" => $ano,
            "cidade" => $cidade,
            "generos" => $generos,
            "mbid" => $artist['id'],
            "discografia" => buscarDiscografiaArtista($artist['id'])
        ];
    }

    function buscarDiscografiaArtista($artistMbid) {

        $url =
            "https://musicbrainz.org/ws/2/release-group?" .
            "artist=" . $artistMbid .
            "&fmt=json" .
            "&limit=100";

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: breve-sonoro/1.0"
            ]
        ];

        $context = stream_context_create($opts);

        $json = @file_get_contents($url, false, $context);

        if (!$json) {
            return [];
        }

        $data = json_decode($json, true);

        if (empty($data['release-groups'])) {
            return [];
        }

        $albuns = [];

    foreach ($data['release-groups'] as $release) {

            $ano = '';

            if (!empty($release['first-release-date'])) {
                $ano = substr($release['first-release-date'], 0, 4);
            }

            $albuns[] = [
                'titulo'          => $release['title'],
                'ano'             => $ano,
                'mbid'            => $release['id'],
                'primary_type'    => $release['primary-type'] ?? 'Other',
                'secondary_types' => $release['secondary-types'] ?? [],
                'capa'            => "https://coverartarchive.org/release-group/" .
                                    $release['id'] .
                                    "/front-500"
            ];
        }

        usort($albuns, function($a, $b) {
            return strcmp($a['ano'], $b['ano']);
        });

        return $albuns;
    }

    function buscarReleasesDoReleaseGroup($releaseGroupMBID) {

$url = "https://musicbrainz.org/ws/2/release"
     . "?query=release-group:" . urlencode($releaseGroupMBID)
     . "&inc=release-groups+media"
     . "&fmt=json"
     . "&limit=100";


    $dados = musicBrainzRequest($url);

    

    if (empty($dados['releases'])) {
        return [];
    }

    $palavrasRuins = [
        'deluxe',
        'anniversary',
        'expanded',
        'bonus',
        'collector',
        'remaster'
    ];

    $releases = [];

    foreach ($dados['releases'] as $release) {

        $ano = 9999;

        if (!empty($release['date'])) {
            $ano = (int) substr($release['date'], 0, 4);
        }

        $titulo = $release['release-group']['title'] 
        ?? $release['title'] 
        ?? '';

        $descricao = $release['disambiguation'] 
          ?? $release['release-group']['disambiguation']
          ?? '';

        $status = $release['status'] ?? '';

        $pais = $release['country'] ?? '';

        $formato = '';

        $faixas = 9999;

        if (!empty($release['media'][0]['format'])) {
            $formato = $release['media'][0]['format'];
        }

        if (!empty($release['media'])) {

            $faixas = 0;

            foreach ($release['media'] as $media) {

                if (!empty($media['track-count'])) {
                    $faixas += (int)$media['track-count'];
                }

            }

        }

        $penalidade = 0;

        if (strcasecmp($status, 'Official') !== 0) {
            $penalidade += 1000;
        }

            $textoAnalise = mb_strtolower(
                $titulo . ' ' . $descricao
            );

            foreach ($palavrasRuins as $palavra) {

            if (strpos($textoAnalise, $palavra) !== false) {
                $penalidade += 500;
                break;
            }

    }

        $releases[] = [

            'mbid' => $release['id'],

            'titulo' => $titulo,

            'descricao' => $descricao,

            'ano' => $ano == 9999 ? null : $ano,

            'pais' => $pais,

            'status' => $status,

            'formato' => $formato,

            'faixas' => $faixas == 9999 ? null : $faixas,

            '_score' => [
                $penalidade,
                $ano,
                $faixas
            ]

        ];

    }

    usort($releases, function ($a, $b) {

        return $a['_score'] <=> $b['_score'];

    });

    foreach ($releases as $i => &$release) {

        $release['recomendada'] = ($i === 0);

        unset($release['_score']);

    }

    return $releases;

}

    /*Legada, essa função sera removida na etapa 4*/ function buscarReleaseDoReleaseGroup($releaseGroupMBID) {

        $url = "https://musicbrainz.org/ws/2/release?release-group=$releaseGroupMBID&fmt=json&limit=1";

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: breve-sonoro/1.0"
            ]
        ];

        $context = stream_context_create($opts);

        $json = @file_get_contents($url, false, $context);

        if (!$json) {
            return null;
        }

        $data = json_decode($json, true);

        if (empty($data['releases'][0]['id'])) {
            return null;
        }

        return $data['releases'][0]['id'];
    }

    function baixarCapaFanart($bandaNome, $albumNome) {

        $apiKey = $_ENV['FANART_API_KEY'] ?? null;

        // 🔥 Buscar álbum no MusicBrainz pra pegar MBID correto
        $resultados = buscarAlbumMusicBrainz($albumNome, $bandaNome);

        if (empty($resultados)) {
            return null;
        }

        $mbidAlbum = $resultados[0]['mbid'];

        // 🔥 Agora usar MBID do álbum (CORRETO)
        $url = "https://webservice.fanart.tv/v3/music/albums/$mbidAlbum?api_key=$apiKey";

        $json = @file_get_contents($url);

        if (!$json) return null;

        $data = json_decode($json, true);

        if (empty($data['albums'][$mbidAlbum]['albumcover'])) {
            return null;
        }

        $covers = $data['albums'][$mbidAlbum]['albumcover'];

        // 🔥 pega a melhor (primeira)
        $imagemUrl = $covers[0]['url'] ?? null;

        if (!$imagemUrl) return null;

        $imagem = @file_get_contents($imagemUrl);

        if (!$imagem) return null;

        // 🔥 limpar nome (igual seu padrão)
        $nomeBase = $albumNome . $bandaNome;
        $nomeBase = str_replace(' ', '', $nomeBase);
        $nomeBase = iconv('UTF-8', 'ASCII//TRANSLIT', $nomeBase);
        $nomeBase = preg_replace('/[^A-Za-z0-9]/', '', $nomeBase);
        $nomeBase = strtolower($nomeBase);

        $nomeFinal = $nomeBase . "_fanart.webp";

        $destino = __DIR__ . "/../uploads/capas/" . $nomeFinal;

        $img = imagecreatefromstring($imagem);

        if (!$img) return null;

        imagewebp($img, $destino, 90);

        imagedestroy($img);

        return $nomeFinal;
    }

    function buscarCapasFanart($bandaNome, $albumNome) {

        $apiKey = $_ENV['FANART_API_KEY'] ?? null;

        $resultados = buscarAlbumMusicBrainz($albumNome, $bandaNome);

        if (empty($resultados)) return [];

        $mbidAlbum = $resultados[0]['mbid'];

        $url = "https://webservice.fanart.tv/v3/music/albums/$mbidAlbum?api_key=$apiKey";

        $json = @file_get_contents($url);

        if (!$json) return [];

        $data = json_decode($json, true);

        if (empty($data['albums'])) return [];

        $albumData = array_values($data['albums'])[0];

        if (empty($albumData['albumcover'])) return [];

        $capas = [];

        foreach ($albumData['albumcover'] as $cover) {
            if (!empty($cover['url'])) {
                $capas[] = $cover['url'];
            }
        }

        return $capas;
    }


    function obterUrlCapaMusicBrainz($mbid) {

        if (empty($mbid)) {
            return null;
        }

        // Cover Art Archive oficial
        $url = "https://coverartarchive.org/release-group/$mbid/front-500";

        // testa se existe
        $headers = @get_headers($url);

        if ($headers && strpos($headers[0], '200') !== false) {
            return $url;
        }

        return null;
    }

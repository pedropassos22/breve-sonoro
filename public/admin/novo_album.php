<?php
require __DIR__ . '/../_init.php';

// Só pode acessar se estiver logado
verificarLogin();

// Só admin pode cadastrar álbum
verificarAdmin();

    $mensagem = "";
    if (isset($_SESSION['flash_sucesso'])) {
        $mensagem = $_SESSION['flash_sucesso'];
        unset($_SESSION['flash_sucesso']);
    }

    $titulo = "";
    $banda_id = "";
    $ano = "";
    $mbid = $_GET['mbid'] ?? null;
    $release_mbid = null;

    /*
    ================================
    FUNÇÕES AUXILIARES BANDAS
    ================================
    */

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

    /*
    ================================
    BUSCA MUSICBRAINZ
    ================================
    */

    $resultados = [];
    $resultadoBanda = null;

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['album'])) {

        $albumBusca = trim($_GET['album']);
        $artistaBusca = trim($_GET['artista'] ?? '');

        if ($albumBusca !== '') {
            $resultados = buscarAlbumMusicBrainz($albumBusca, $artistaBusca);
        }
    }

    /*
    ================================
    BUSCAR BANDA MUSICBRAINZ
    ================================
    */

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['buscar_banda'])) {

            $bandaBusca = trim($_GET['buscar_banda']);

            if ($bandaBusca !== '') {
                $resultadoBanda = buscarBandaMusicBrainz($bandaBusca);
            }
        }

        // Buscar MBIDs já cadastrados no banco
        $mbidsCadastrados = [];
        $stmtMbids = $pdo->query("SELECT mbid FROM albuns WHERE mbid IS NOT NULL AND mbid != ''");
        foreach ($stmtMbids as $row) {
            $mbidsCadastrados[] = $row['mbid'];
    }


    // Preenchimento automático vindo do MusicBrainz
    if (isset($_GET['titulo'])) {
        $titulo = $_GET['titulo'];
    }

    if (isset($_GET['ano'])) {
        $ano = $_GET['ano'];
    }

    $banda_nome_importada = $_GET['banda_nome'] ?? "";



    /*
    ================================
    CADASTRAR NOVA BANDA
    ================================
    */

    if (isset($_POST['salvar_banda'])) {

        $nome = trim($_POST['nome']);

        $ano_formacao =
            !empty($_POST['ano_formacao'])
            ? $_POST['ano_formacao']
            : null;

        $cidade =
            !empty($_POST['cidade'])
            ? trim($_POST['cidade'])
            : null;

        if (empty($nome)) {

            $mensagem = "O nome da banda é obrigatório.";

        } else {

            $slug = gerarSlug($nome);
            $nome_normalizado = normalizarNome($nome);

            $stmt = $pdo->prepare("
                SELECT id FROM bandas
                WHERE nome_normalizado = ?
            ");

            $stmt->execute([$nome_normalizado]);

            if ($stmt->rowCount() > 0) {

                $mensagem = "Essa banda já está cadastrada.";

            } else {

                $stmt = $pdo->prepare("
                    INSERT INTO bandas (
                        nome,
                        slug,
                        nome_normalizado,
                        ano_formacao,
                        cidade
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $nome,
                    $slug,
                    $nome_normalizado,
                    $ano_formacao,
                    $cidade
                ]);

                $banda_id_nova = $pdo->lastInsertId();

                if (!empty($_POST['generos'])) {

                    foreach ($_POST['generos'] as $genero_id) {

                        $stmtGenero = $pdo->prepare("
                            INSERT INTO banda_genero (
                                banda_id,
                                genero_id
                            )
                            VALUES (?, ?)
                        ");

                        $stmtGenero->execute([
                            $banda_id_nova,
                            $genero_id
                        ]);
                    }
                }

                $mensagem = "Banda cadastrada com sucesso!";
            }
        }
    }



    // Se o formulário foi enviado
    if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && !isset($_POST['salvar_banda'])
) {

    $titulo = trim($_POST['titulo'] ?? '');
    $banda_id = $_POST['banda_id'] ?? '';

    $mbid = $_POST['mbid'] ?? null;
    $release_mbid = $_POST['release_mbid'] ?? null;

    // se banda não foi selecionada mas veio do MusicBrainz
    if (empty($banda_id) && !empty($banda_nome_importada)) {

        // verificar se banda já existe
        $stmt = $pdo->prepare("
            SELECT id FROM bandas 
            WHERE nome = ?
            LIMIT 1
        ");

        $stmt->execute([$banda_nome_importada]);
        $bandaExistente = $stmt->fetch();

        if ($bandaExistente) {

            // usar banda existente
            $banda_id = $bandaExistente['id'];

        } else {

            // criar nova banda automaticamente
// normalizar nome da banda
$normalizado = strtolower($banda_nome_importada);
$normalizado = str_replace(' ', '', $normalizado);

// verificar se banda já existe
$stmt = $pdo->prepare("
    SELECT id FROM bandas 
    WHERE nome_normalizado = ?
    LIMIT 1
");

$stmt->execute([$normalizado]);
$bandaExistente = $stmt->fetch();

if ($bandaExistente) {

    $banda_id = $bandaExistente['id'];

} else {

    $stmt = $pdo->prepare("
        INSERT INTO bandas (nome, nome_normalizado)
        VALUES (?, ?)
    ");

    $stmt->execute([
        $banda_nome_importada,
        $normalizado
    ]);

    $banda_id = $pdo->lastInsertId();
}
        }
    }
    $banda_nome_nova = $_POST['banda_nome_nova'] ?? null;

    if (empty($banda_id) && !empty($banda_nome_nova)) {

        $stmt = $pdo->prepare("
            INSERT INTO bandas (nome)
            VALUES (?)
        ");

        $stmt->execute([$banda_nome_nova]);

        $banda_id = $pdo->lastInsertId();
    }
    $ano = trim($_POST['ano'] ?? '');

    $anoAtual = date('Y');

    if (empty($banda_id)) {
    $mensagem = "Selecione uma banda.";
    } elseif (!preg_match('/^\d{4}$/', $ano)) {
        $mensagem = "O ano deve conter exatamente 4 dígitos.";
    } elseif ($ano < 1900) {
        $mensagem = "O ano não pode ser menor que 1900.";
    } elseif ($ano > $anoAtual) {
        $mensagem = "O ano não pode ser maior que o ano atual.";
    }

$capa_nome = null;

/*
================================
2️⃣ FALLBACK → UPLOAD MANUAL
================================
*/

if (!$capa_nome && !empty($_FILES['capa']['name'])) {

    $arquivoTmp = $_FILES['capa']['tmp_name'];

    $info = getimagesize($arquivoTmp);

    if ($info === false) {

        $mensagem = "Arquivo enviado não é uma imagem válida.";

    } else {

        $largura  = $info[0];
        $altura   = $info[1];
        $mime     = $info['mime'];

        if ($largura < 500 || $altura < 500) {
            $mensagem = "A imagem deve ter no mínimo 500x500 pixels.";
        } elseif ($largura !== $altura) {
            $mensagem = "A imagem deve ser quadrada (1:1).";
        } else {

            switch ($mime) {
                case 'image/jpeg':
                    $imagemOriginal = imagecreatefromjpeg($arquivoTmp);
                    break;
                case 'image/png':
                    $imagemOriginal = imagecreatefrompng($arquivoTmp);
                    break;
                case 'image/webp':
                    $imagemOriginal = imagecreatefromwebp($arquivoTmp);
                    break;
                default:
                    $imagemOriginal = false;
                    $mensagem = "Formato não permitido.";
            }

            if ($imagemOriginal) {

                $novoTamanho = 500;

                $imagemRedimensionada =
                    imagecreatetruecolor($novoTamanho, $novoTamanho);

                imagecopyresampled(
                    $imagemRedimensionada,
                    $imagemOriginal,
                    0,0,0,0,
                    $novoTamanho,$novoTamanho,
                    $largura,$altura
                );

                $capa_nome =
                    "uploads/capas/" .
                    uniqid("capa_", true) .
                    ".webp";

                imagewebp(
                    $imagemRedimensionada,
                    __DIR__ . "/" . $capa_nome,
                    80
                );

                imagedestroy($imagemOriginal);
                imagedestroy($imagemRedimensionada);
            }
        }
    }
}






        $check = $pdo->prepare("
            SELECT id FROM albuns 
            WHERE titulo = ? AND banda_id = ?
            LIMIT 1
        ");

        $check->execute([$titulo, $banda_id]);


        if ($check->fetch()) {
            $mensagem = "Já existe um álbum com esse título e artista.";
        }


if (empty($mensagem)) {


    $stmt = $pdo->prepare("
    INSERT INTO albuns (titulo, banda_id, ano, criado_por, capa, mbid)
    VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $titulo,
        $banda_id,
        $ano,
        $_SESSION['usuario_id'],
        $capa_nome,
        $mbid
    ]);

    // 🔥 pegar id do álbum recém criado
    $album_id = $pdo->lastInsertId();

// Importar faixas do MusicBrainz usando o Release MBID escolhido
    if (!empty($release_mbid)) {

        $faixas = buscarFaixasAlbum($release_mbid);

        if (!empty($faixas)) {
            salvarFaixasAlbum($pdo, $album_id, $faixas);
        }

    }
    




    $mensagem = "Álbum cadastrado com sucesso!";
    $_SESSION['flash_sucesso'] = "Álbum cadastrado com sucesso!";
    header("Location: novo_album.php");
    exit;

    }

    


}


?>
<!DOCTYPE html>
<html>
<head>
    <title>Novo Álbum - breve-sonoro</title>
    <link rel="stylesheet" href="assets/css/layout.css">
</head>
<body>
    <div class="container-cru">

        <h2>Cadastrar Novo Álbum</h2>
        <hr>

        <h3>Buscar álbum no MusicBrainz</h3>

        <form method="GET">

        Álbum:
        <input type="text" name="album">

        Artista:
        <input type="text" name="artista">

        <button type="submit">Buscar</button>

        </form>

        <hr>


        <h3>Buscar Banda no MusicBrainz</h3>

        <form method="GET">

            Nome da banda:
            <input type="text" name="buscar_banda">

            <button type="submit">
                Buscar Banda
            </button>

        </form>

        <br>

        <?php if ($resultadoBanda): ?>

            <div style="margin-bottom:30px;">

                <strong>
                    <?php echo htmlspecialchars($resultadoBanda['nome']); ?>
                </strong>

                <br><br>

                Ano de formação:
                <?php
                echo htmlspecialchars(
                    $resultadoBanda['ano_formacao']
                    ?? "Não informado"
                );
                ?>

                <br><br>

                Cidade / País:
                <?php
                echo htmlspecialchars(
                    $resultadoBanda['cidade']
                    ?? "Não informado"
                );
                ?>

                <br><br>

                Gêneros:

                <ul>

                <?php
                if (!empty($resultadoBanda['generos'])) {

                    foreach ($resultadoBanda['generos'] as $genero) {

                        echo "<li>" .
                            htmlspecialchars($genero) .
                            "</li>";
                    }
                }
                ?>

                </ul>

            </div>

            <hr>

            <h4>Discografia</h4>

<?php

$secoes = [
    'Album'       => [],
    'Single'      => [],
    'EP'          => [],
    'Other'       => [],
    'Live'        => [],
    'Compilation' => [],
    'Remix'       => [],
    'Outros'      => [],
];

$secundariosConhecidos = ['Live', 'Compilation', 'Remix'];

foreach ($resultadoBanda['discografia'] as $album) {

    $primary   = $album['primary_type'] ?? 'Other';
    $secondary = $album['secondary_types'] ?? [];

    $encaixou = false;

    foreach ($secundariosConhecidos as $sec) {
        if (in_array($sec, $secondary)) {
            $secoes[$sec][] = $album;
            $encaixou = true;
            break;
        }
    }

    if (!$encaixou) {
        if (isset($secoes[$primary])) {
            $secoes[$primary][] = $album;
        } else {
            $secoes['Outros'][] = $album;
        }
    }
}

foreach ($secoes as $secaoNome => $albuns):

    if (empty($albuns)) continue;

?>

<h5 style="margin:25px 0 10px; border-bottom:1px solid #444; padding-bottom:5px;">
    <?= htmlspecialchars($secaoNome) ?>
    <span style="font-weight:normal; font-size:12px; color:#888;">
        (<?= count($albuns) ?>)
    </span>
</h5>

<div style="display:flex; flex-wrap:wrap; gap:15px;">

<?php foreach ($albuns as $album):

    $jaCadastrado = in_array($album['mbid'], $mbidsCadastrados);

?>


<a href="#"
class="album-release"
data-mbid="<?= htmlspecialchars($album['mbid']) ?>"
data-titulo="<?= htmlspecialchars($album['titulo']) ?>"
data-banda="<?= htmlspecialchars($resultadoBanda['nome']) ?>"
data-ano="<?= htmlspecialchars($album['ano']) ?>"
style="
text-decoration:none;
color:#fff;
width:160px;
opacity:<?= $jaCadastrado ? '0.5' : '1' ?>;
pointer-events:<?= $jaCadastrado ? 'none' : 'auto' ?>;
display:inline-block;
"
>

<?php if (!empty($album['capa'])): ?>

<img
src="<?= htmlspecialchars($album['capa']) ?>"
style="
width:160px;
height:160px;
object-fit:cover;
border-radius:8px;
display:block;
"
/>

<?php if ($jaCadastrado): ?>
<div style="
background:#2a9d5c;
color:#fff;
font-size:11px;
font-weight:bold;
text-align:center;
padding:3px 0;
border-radius:0 0 8px 8px;
margin-top:-6px;
">✔ Cadastrado</div>
<?php endif; ?>

<?php else: ?>

<div style="
width:160px;
height:160px;
background:#333;
display:flex;
align-items:center;
justify-content:center;
border-radius:8px;
">Sem capa</div>

<?php endif; ?>

<div style="margin-top:5px; font-size:13px;">
    <?= htmlspecialchars($album['titulo']) ?>
</div>

<div style="font-size:12px; color:#888;">
    <?= htmlspecialchars($album['ano']) ?>
</div>

</a>

<?php endforeach; ?>

</div>

<?php endforeach; ?>

        <?php endif; ?>

        <hr>

        <div id="lista-releases" style="
        margin:30px 0;
        padding:20px;
        border:1px solid #444;
        display:none;
        ">

        <h3>Escolha uma edição</h3>

        <div id="conteudo-releases">
        </div>

        </div>

        <h3>Cadastrar Nova Banda</h3>

        <form method="POST">

            <label>Nome da Banda:</label><br>

            <input
                type="text"
                name="nome"
                required
                style="width:300px;"
            >

            <br><br>

            <label>Ano de Formação:</label><br>

            <input
                type="number"
                name="ano_formacao"
                min="1900"
                max="<?php echo date('Y'); ?>"
                style="width:150px;"
            >

            <br><br>

            <label>Cidade:</label><br>

            <input
                type="text"
                name="cidade"
                style="width:300px;"
            >

            <br><br>

            <label>Gêneros:</label><br>

            <select
                name="generos[]"
                multiple
                required
                style="width:300px; height:120px;"
            >

                <?php

                $stmt = $pdo->query("
                    SELECT id, nome
                    FROM generos
                    WHERE ativo = 1
                    ORDER BY nome
                ");

                while ($genero = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    echo "
                    <option value='{$genero['id']}'>
                        {$genero['nome']}
                    </option>
                    ";
                }

                ?>

            </select>

            <br><br>

            <button type="submit" name="salvar_banda">
                Salvar Banda
            </button>

        </form>

        <hr>    



        <?php if ($mensagem): ?>

            <?php if (strpos($mensagem, "sucesso") !== false): ?>
                <p style="color:green;"><?php echo $mensagem; ?></p>
            <?php else: ?>
                <p style="color:red;"><?php echo $mensagem; ?></p>
            <?php endif; ?>

        <?php endif; ?>


        <?php foreach ($resultados as $r): ?>

        <div style="margin-bottom:20px;">

        <?php

        $titulo_r = $r['titulo'] ?? 'Sem título';
        $artista_nome_r = $r['artista'] ?? 'Desconhecido';
        $ano_r = $r['ano'] ?? '';
        $mbid_r = htmlspecialchars($r['mbid'] ?? '');

        ?>

        <strong><?php echo htmlspecialchars($titulo_r); ?></strong><br>

        Artista:
        <?php echo htmlspecialchars($artista_nome_r); ?><br>

        Ano:
        <?php echo $ano_r; ?><br><br>

        MBID:
        <?php echo $mbid_r; ?><br><br>

        <a href="novo_album.php?titulo=<?php echo urlencode($titulo_r); ?>&banda_nome=<?php echo urlencode($artista_nome_r); ?>&ano=<?php echo urlencode($ano_r); ?>&mbid=<?php echo urlencode($mbid_r); ?>">
        Importar este álbum
        </a>

        </div>

        <?php endforeach; ?>

        <form method="POST" enctype="multipart/form-data">
                <?php if (!empty($banda_nome_importada)): ?>
                <input type="hidden" name="banda_nome_nova" value="<?php echo htmlspecialchars($banda_nome_importada); ?>">
                <?php endif; ?>

                <input type="hidden" name="mbid" id="mbid">
                <input type="hidden" name="release_mbid" id="release_mbid">



                <label>Título:</label><br>
                <input 
                type="text" 
                name="titulo" 
                value="<?php echo htmlspecialchars($titulo); ?>" 
                required 
                style="width:300px;"><br><br>

                <label>Banda:</label><br>

                <select name="banda_id" required>

                    <option value="">Selecione a banda</option>

                    <?php if (!empty($banda_nome_importada)): ?>
                    <option value="">
                    <?php echo htmlspecialchars($banda_nome_importada); ?> (criar nova banda)
                    </option>
                    <?php endif; ?>


                    <?php
                    $stmtBandas = $pdo->query("SELECT * FROM bandas ORDER BY nome ASC");
                    foreach ($stmtBandas as $banda):
                    ?>
                <option 
                    value="<?php echo $banda['id']; ?>"
                    <?php if ($banda_id == $banda['id']) echo "selected"; ?>
                >

                            <?php echo htmlspecialchars($banda['nome']); ?>
                        </option>
                    <?php endforeach; ?>

                </select>
        <br><br>



            <label>Ano:</label><br>
            <input 
            type="text" 
            name="ano" 
            value="<?php echo htmlspecialchars($ano); ?>"
            maxlength="4" 
            pattern="\d{4}" 
            inputmode="numeric"
            placeholder="Ex: 1998" 
            style="width:100px;" 
            required>


            <label>Capa do Álbum:</label><br>
            <input type="file" name="capa" id="capaInput" accept="image/*"><br><br>

            <img id="preview" src="" style="display:none; width:200px; border-radius:8px;"><br><br>



            <button type="submit">Cadastrar</button>
        </form>

        <br>
                <a href="admin/admin.php" class="dash-btn">
                    Voltar
                </a>


            <script>
            document.getElementById("capaInput").addEventListener("change", function(event) {

            

                const file = event.target.files[0];
                const preview = document.getElementById("preview");

                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = "block";
                    }

                    reader.readAsDataURL(file);
                }
            });

            
            </script>
           
           <script>

                document.querySelectorAll(".album-release").forEach(function(item){

                    item.addEventListener("click", function(e){

                        e.preventDefault();

                        const mbid = this.dataset.mbid;
                        const ano = this.dataset.ano;

                        console.log("MBID clicado:", mbid);
                        console.log("Ano do álbum:", ano);

                        fetch(
                            "buscar_releases.php?mbid=" +
                            encodeURIComponent(mbid) +
                            "&ano=" +
                            encodeURIComponent(ano)
                        )

                        .then(r => r.json())

                        .then(resposta => {

                            if(!resposta.sucesso){
                                alert("Erro ao buscar releases.");
                                return;
                            }

                            let html = "";

                            resposta.dados.forEach(function(release){

                                html += `
                                <div style="
                                    border:1px solid #666;
                                    padding:12px;
                                    margin-bottom:10px;
                                    ${release.recomendada ? "background:#234d23;" : ""}
                                ">

                                    <strong>${release.titulo}</strong><br>

                                        Ano: ${release.ano ?? "-"}<br>

                                        País: ${release.pais}<br>

                                        Formato: ${release.formato}<br>

                                        Status: ${release.status}<br>

                                        Faixas: ${release.faixas}<br><br>

                                        <button
                                            type="button"
                                            class="btn-importar-release"
                                            data-release-mbid="${release.mbid}"
                                            data-release-group-mbid="${mbid}"
                                            data-titulo="${item.dataset.titulo}"
                                            data-banda="${item.dataset.banda}"
                                            data-ano="${item.dataset.ano}"
                                        >
                                            Importar esta edição
                                        </button>

                                        </div>
                                `;

                            });

                            document.getElementById("conteudo-releases").innerHTML = html;

                            document.getElementById("lista-releases").style.display = "block";

                            document.getElementById("lista-releases")
                                .scrollIntoView({
                                    behavior:"smooth"
                                });

                        })

                        .catch(function(erro){

                            console.error("Erro ao buscar releases:", erro);

                            alert(
                                "Não foi possível buscar as edições do álbum.\n\n" +
                                "Verifique o Console (F12) para ver o erro."
                            );

                        });

                    });

                });



                                                        document.addEventListener("click", function(e){

                                                            if (!e.target.classList.contains("btn-importar-release")) {
                                                                return;
                                                            }

                                                            const botao = e.target;

                                                            const releaseMbid = botao.dataset.releaseMbid;
                                                            const releaseGroupMbid = botao.dataset.releaseGroupMbid;
                                                            const titulo = botao.dataset.titulo;
                                                            const banda = botao.dataset.banda;
                                                            const ano = botao.dataset.ano;

                                                            console.log("Release MBID escolhido:", releaseMbid);
                                                            console.log("Release Group MBID:", releaseGroupMbid);

                                                            document.getElementById("mbid").value = releaseGroupMbid;

                                                            document.getElementById("release_mbid").value = releaseMbid;

                                                            document.querySelector('input[name="titulo"]').value = titulo;

                                                            document.querySelector('input[name="ano"]').value = ano;

                                                        });


            </script>
    </div>

</body>
</html>

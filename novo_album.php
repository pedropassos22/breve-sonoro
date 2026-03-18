<?php
require "includes/config.php";
require "includes/session.php";
require "includes/musicbrainz.php";

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


    // Preenchimento automático vindo do MusicBrainz
    if (isset($_GET['titulo'])) {
        $titulo = $_GET['titulo'];
    }

    if (isset($_GET['ano'])) {
        $ano = $_GET['ano'];
    }

    $banda_nome_importada = $_GET['banda_nome'] ?? "";



    // Se o formulário foi enviado
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo    = trim($_POST['titulo']);
    $banda_id  = $_POST['banda_id'];

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
        $stmt = $pdo->prepare("
            INSERT INTO bandas (nome)
            VALUES (?)
        ");

        $stmt->execute([$banda_nome_importada]);

        $banda_id = $pdo->lastInsertId();
    }
}

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
    $ano       = trim($_POST['ano']);

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

    // 🔥 SE NÃO ENVIAR CAPA → TENTA BAIXAR DO MUSICBRAINZ
    if (empty($_FILES['capa']['name']) && !empty($mbid)) {

        // buscar nome da banda
        // tentar pegar nome da banda
            $bandaNome = "";

            // se já selecionou banda existente
            if (!empty($banda_id)) {

                $stmtBanda = $pdo->prepare("SELECT nome FROM bandas WHERE id = ?");
                $stmtBanda->execute([$banda_id]);
                $banda = $stmtBanda->fetch();

                if ($banda) {
                    $bandaNome = $banda['nome'];
                }

            }

            // se veio do MusicBrainz
            if (empty($bandaNome) && !empty($banda_nome_importada)) {
                $bandaNome = $banda_nome_importada;
            }

            $capa_nome = baixarCapaMusicBrainz($mbid, $titulo, $bandaNome);

        if (!$capa_nome) {
            $mensagem = "Não foi possível baixar a capa automaticamente.";
        }

    } elseif (empty($_FILES['capa']['name'])) {

        $mensagem = "A capa do álbum é obrigatória.";

    } else {

    $arquivoTmp = $_FILES['capa']['tmp_name'];

    $info = getimagesize($arquivoTmp);

    if ($info === false) {
        $mensagem = "Arquivo enviado não é uma imagem válida.";
    } else {

        $largura  = $info[0];
        $altura   = $info[1];
        $mime     = $info['mime'];

        // 🔥 VALIDAR TAMANHO MÍNIMO
        // 🔥 VALIDAR TAMANHO MÍNIMO
        if ($largura < 500 || $altura < 500) {

            $mensagem = "A imagem deve ter no mínimo 500x500 pixels.";

        // 🔥 VALIDAR TAMANHO MÁXIMO
        } elseif ($largura > 2000 || $altura > 2000) {

            $mensagem = "A imagem não pode ser maior que 2000x2000 pixels.";

        // 🔥 VALIDAR PROPORÇÃO 1:1
        } elseif ($largura !== $altura) {

            $mensagem = "A imagem deve ser quadrada (proporção 1:1).";

        } else {

            // 🔥 CRIAR IMAGEM ORIGINAL
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
                    $mensagem = "Formato de imagem não permitido.";
                    $imagemOriginal = false;
            }

            if ($imagemOriginal) {

                $novoTamanho = 500;

                $imagemRedimensionada = imagecreatetruecolor($novoTamanho, $novoTamanho);

                imagecopyresampled(
                    $imagemRedimensionada,
                    $imagemOriginal,
                    0, 0, 0, 0,
                    $novoTamanho, $novoTamanho,
                    $largura, $altura
                );

                // 🔥 Buscar nome da banda
                $stmtBanda = $pdo->prepare("SELECT nome FROM bandas WHERE id = ?");
                $stmtBanda->execute([$banda_id]);
                $banda = $stmtBanda->fetch();

                $nomeBase = $titulo . $banda['nome'];


                // remover espaços
                $nomeBase = str_replace(' ', '', $nomeBase);

                // remover acentos
                $nomeBase = iconv('UTF-8', 'ASCII//TRANSLIT', $nomeBase);

                // remover caracteres especiais
                $nomeBase = preg_replace('/[^A-Za-z0-9]/', '', $nomeBase);

                // deixar tudo minúsculo
                $nomeBase = strtolower($nomeBase);

                $capa_nome = $nomeBase . ".webp";


                $destino = __DIR__ . "/uploads/capas/" . $capa_nome;

                // 🔥 Se já existir arquivo com mesmo nome → erro
                if (file_exists($destino)) {
                    $mensagem = "Já existe uma capa com esse nome. Verifique o título e artista.";
                }


                // 🔥 SALVA EM WEBP COM QUALIDADE 80
                if (empty($mensagem)) {
                imagewebp($imagemRedimensionada, $destino, 80);
                 }


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


if (empty($mensagem) && $capa_nome !== null) {

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

    // 🔥 importar faixas do MusicBrainz se houver MBID
    if (!empty($mbid)) {

        $faixas = buscarFaixasAlbum($mbid);

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

        <?php if ($mensagem): ?>

            <?php if (strpos($mensagem, "sucesso") !== false): ?>
                <p style="color:green;"><?php echo $mensagem; ?></p>
            <?php else: ?>
                <p style="color:red;"><?php echo $mensagem; ?></p>
            <?php endif; ?>

        <?php endif; ?>


        <form method="POST" enctype="multipart/form-data">
                <?php if (!empty($banda_nome_importada)): ?>
                <input type="hidden" name="banda_nome_nova" value="<?php echo htmlspecialchars($banda_nome_importada); ?>">
                <?php endif; ?>


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
    </div>

</body>
</html>

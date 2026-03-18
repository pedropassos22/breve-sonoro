<?php
require "includes/config.php";
require "includes/session.php";
require "includes/musicbrainz.php";


verificarLogin();
verificarAdmin();

if (!isset($_GET['album_id'])) {
    exit("Álbum não especificado.");
}

$album_id = $_GET['album_id'];

$stmt = $pdo->prepare("
    SELECT * FROM faixas
    WHERE album_id = ?
    ORDER BY numero ASC
");
$stmt->execute([$album_id]);
$faixas = $stmt->fetchAll();


$mensagem = "";

if (isset($_POST['salvar_capa'])) {

    if (isset($_FILES['capa']) && $_FILES['capa']['error'] === 0) {

        $extensao = pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION);
        $extensao = strtolower($extensao);

        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extensao, $permitidas)) {

            // 🔥 Buscar nome do álbum e banda
           $stmtInfo = $pdo->prepare("
            SELECT albuns.titulo AS album_nome, bandas.nome AS banda_nome
            FROM albuns
            INNER JOIN bandas ON albuns.banda_id = bandas.id
            WHERE albuns.id = ?
        ");

            $stmtInfo->execute([$album_id]);
            $infoAlbum = $stmtInfo->fetch();

            $bandaNome = $infoAlbum['banda_nome'];
            $albumNome = $infoAlbum['album_nome'];

            // 🔥 Função para limpar nome
            function limparNomeArquivo($texto) {

                $texto = strtolower($texto);

                // remover acentos
                $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);

                // remover caracteres especiais
                $texto = preg_replace('/[^a-z0-9]/', '', $texto);

                return $texto;
            }

            $bandaLimpa = limparNomeArquivo($bandaNome);
            $albumLimpo = limparNomeArquivo($albumNome);

            // 🔥 Novo nome final
            $novoNome = $bandaLimpa . $albumLimpo . ".webp";

            $pasta = 'uploads/capas/';
            $caminho = $pasta . $novoNome;

            if (!is_dir($pasta)) {
                mkdir($pasta, 0755, true);
            }

            if (move_uploaded_file($_FILES['capa']['tmp_name'], $caminho)) {

            // 🔥 Buscar capa antiga
                $stmtAntiga = $pdo->prepare("SELECT capa FROM albuns WHERE id = ?");
                $stmtAntiga->execute([$album_id]);
                $albumAntigo = $stmtAntiga->fetch();

                if ($albumAntigo && !empty($albumAntigo['capa'])) {

                    $caminhoAntigo = __DIR__ . "/uploads/capas/" . $albumAntigo['capa'];

                    if (file_exists($caminhoAntigo)) {
                        unlink($caminhoAntigo); // apaga a imagem antiga
                    }
                }

                $stmt = $pdo->prepare("UPDATE albuns SET capa = ? WHERE id = ?");
                $stmt->execute([$novoNome, $album_id]);

                $mensagem = "Capa atualizada com sucesso!";
            }

        } else {
            $mensagem = "Formato inválido. Use JPG, PNG ou WEBP.";
        }

    } else {
        $mensagem = "Erro no upload.";
    }
}

if (isset($_POST['buscar_fanart'])) {

    $stmtInfo = $pdo->prepare("
        SELECT albuns.titulo AS album_nome, bandas.nome AS banda_nome
        FROM albuns
        INNER JOIN bandas ON albuns.banda_id = bandas.id
        WHERE albuns.id = ?
    ");

    $stmtInfo->execute([$album_id]);
    $infoAlbum = $stmtInfo->fetch();

    $bandaNome = $infoAlbum['banda_nome'];
    $albumNome = $infoAlbum['album_nome'];

    $capasFanart = buscarCapasFanart($bandaNome, $albumNome);

    if (empty($capasFanart)) {
    $mensagem = "Não foi possível encontrar capas no Fanart.";
}

}


if (isset($_POST['salvar_capa_fanart'])) {

    $url = $_POST['capa_url'];

    $imagem = @file_get_contents($url);

    if ($imagem) {

        $stmtInfo = $pdo->prepare("
            SELECT albuns.titulo AS album_nome, bandas.nome AS banda_nome
            FROM albuns
            INNER JOIN bandas ON albuns.banda_id = bandas.id
            WHERE albuns.id = ?
        ");

        $stmtInfo->execute([$album_id]);
        $infoAlbum = $stmtInfo->fetch();

        $nomeBase = $infoAlbum['album_nome'] . $infoAlbum['banda_nome'];
        $nomeBase = str_replace(' ', '', $nomeBase);
        $nomeBase = iconv('UTF-8', 'ASCII//TRANSLIT', $nomeBase);
        $nomeBase = preg_replace('/[^A-Za-z0-9]/', '', $nomeBase);
        $nomeBase = strtolower($nomeBase);

        $nomeFinal = $nomeBase . "_fanart.webp";

        $destino = __DIR__ . "/uploads/capas/" . $nomeFinal;

        $img = imagecreatefromstring($imagem);

        if ($img) {

            imagewebp($img, $destino, 90);
            imagedestroy($img);

            // atualizar banco
            $stmt = $pdo->prepare("UPDATE albuns SET capa = ? WHERE id = ?");
            $stmt->execute([$nomeFinal, $album_id]);

            $mensagem = "Capa escolhida com sucesso!";
        }
    }
}



?>
<!DOCTYPE html>
<html>
<head>
    <title>Nova Faixa - breve-sonoro</title>
    <link rel="stylesheet" href="assets/css/layout.css">
</head>
<body>

    <div class="container-cru">
        <h2>Adicionar Faixa</h2>

            <?php if ($mensagem): ?>
            <p style="color:green;"><?php echo $mensagem; ?></p>
            <?php endif; ?>


            <h3>Alterar Capa do Álbum</h3>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="capa" accept="image/*">
                <button type="submit" name="salvar_capa">Upload Manual</button>
            </form>

<br>

<form method="POST">
    <button type="submit" name="buscar_fanart">
        Buscar capa HD (Fanart.tv)
    </button>
</form>

<?php if (!empty($capasFanart)): ?>

    <h3>Escolha uma capa:</h3>

    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <?php foreach ($capasFanart as $url): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="capa_url" value="<?php echo $url; ?>">
                <button type="submit" name="salvar_capa_fanart" style="border:none; background:none; cursor:pointer;">
                    <img src="<?php echo $url; ?>" style="width:150px; height:150px; object-fit:cover;">
                </button>
            </form>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


</form>


            <br>


        <table border="1" cellpadding="5" id="tabela-faixas">
            <tr>
                <th>Nº</th>
                <th>Nome</th>
                <th>Duração</th>
                <th>Ação</th>
            </tr>

            <?php foreach ($faixas as $faixa): ?>
                <tr data-id="<?php echo $faixa['id']; ?>">
                    <td>
                        <input type="text" value="<?php echo str_pad($faixa['numero'], 2, '0', STR_PAD_LEFT); ?>" disabled>
                    </td>
                    <td>
                        <input type="text" class="nome" value="<?php echo htmlspecialchars($faixa['nome']); ?>">
                    </td>
                    <td>
                        <input type="text" class="duracao" maxlength="5"
                            value="<?php echo $faixa['duracao']; ?>"
                            placeholder="00:00">
                    </td>
                    <td>
                    
                    <form method="POST" action="admin/excluir_faixa.php" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="faixa_id" value="<?php echo $faixa['id']; ?>">
                        <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                        <button type="submit" onclick="return confirm('Deseja excluir esta faixa?')">
                            Excluir
                        </button>
                    </form>


                    </td>
                </tr>

            <?php endforeach; ?>
        </table>



        <br>
        <a href="album.php?id=<?php echo $album_id; ?>">Voltar ao álbum</a>

        <script>
        const albumId = <?php echo $album_id; ?>;
        const tabela = document.getElementById('tabela-faixas');

        // ========================
        // FORMATAR DURAÇÃO 123 → 01:23
        // ========================
        function formatarDuracao(valor) {

            let numeros = valor.replace(/\D/g, '');

            if (numeros.length > 4) {
                numeros = numeros.slice(0, 4);
            }

            if (numeros.length <= 2) {
                return numeros;
            }

            return numeros.slice(0, numeros.length - 2) + ':' + numeros.slice(-2);
        }



        // ========================
        // SALVAR LINHA
        // ========================
        function salvarLinha(tr) {

            const id = tr.dataset.id || null;
            const numero = tr.querySelector('td input').value;
            const nome = tr.querySelector('.nome').value.trim();
            const duracao = tr.querySelector('.duracao').value.trim();

            if (!nome || !duracao) return;

            fetch('salvar_faixa_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: id,
                    album_id: albumId,
                    numero: numero,
                    nome: nome,
                    duracao: duracao
                })
            })
            .then(res => res.json())
            .then(data => {
                tr.dataset.id = data.id;

                // Criar botão excluir se ainda não existir
                const tdAcao = tr.querySelector('td:last-child');

                if (!tdAcao.innerHTML.trim()) {

                    tdAcao.innerHTML = `
                        <form method="POST" action="admin/excluir_faixa.php" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="faixa_id" value="${data.id}">
                            <input type="hidden" name="album_id" value="${albumId}">
                            <button type="submit" onclick="return confirm('Deseja excluir esta faixa?')">
                                Excluir
                            </button>
                        </form>
                    `;
                }
            });

        }

        // ========================
        // CRIAR NOVA LINHA
        // ========================
        function adicionarLinha() {

            const totalLinhas = tabela.querySelectorAll('tr').length - 1;
            const numero = String(totalLinhas + 1).padStart(2, '0');

            const tr = document.createElement('tr');
            tr.dataset.id = '';

            tr.innerHTML = `
                <td><input type="text" value="${numero}" disabled></td>
                <td><input type="text" class="nome"></td>
                <td><input type="text" class="duracao" maxlength="5" placeholder="00:00"></td>
                <td></td>
            `;

            tabela.appendChild(tr);

            ativarEventos(tr);

            tr.querySelector('.nome').focus();
        }

        // ========================
        // ATIVAR EVENTOS
        // ========================
        function ativarEventos(tr) {

            const inputDuracao = tr.querySelector('.duracao');

            inputDuracao.addEventListener('input', function() {
                this.value = formatarDuracao(this.value);
            });

            tr.addEventListener('keydown', function(e) {

                if (e.key === 'Enter') {

                    e.preventDefault();

                    salvarLinha(tr);

                    // Se for a última linha → cria nova
                    if (tr === tabela.querySelectorAll('tr')[tabela.querySelectorAll('tr').length - 1]) {
                        adicionarLinha();
                    }
                }
            });
        }

        // Ativar eventos nas linhas existentes
        document.querySelectorAll('#tabela-faixas tr').forEach((tr, index) => {
            if (index === 0) return; // ignora header
            ativarEventos(tr);
        });

        // Sempre adicionar uma linha vazia no final
        adicionarLinha();
        </script>
    </div>
</body>
</html>

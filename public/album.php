<?php


require __DIR__ . '/../app/includes/bootstrap.php';

verificarLogin();

if (!isset($_GET['id'])) {
    exit("Álbum não especificado.");
}

$album_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$album_id) {
    exit("Álbum inválido.");
}


// ==========================
// 1️⃣ BUSCAR O ÁLBUM
// ==========================

$album = buscarAlbumPorId($pdo, $album_id);


// Verificar se já está na dash
$stmtDash = $pdo->prepare("
    SELECT id FROM usuario_dash
    WHERE usuario_id = ? AND album_id = ?
");

$stmtDash->execute([$_SESSION['usuario_id'], $album_id]);

$estaNaDash = $stmtDash->fetch();


if (!$album) {
    exit("Álbum não encontrado.");
}

// ==========================
// 2️⃣ BUSCAR FAIXAS COM CONTADOR
// ==========================

$usuario_id = $_SESSION['usuario_id'];

$faixas = buscarFaixasDoAlbum($pdo, $album_id, $usuario_id);


// ==========================
// AGRUPAR FAIXAS POR DISCO
// ==========================

$faixas_por_disco = [];

foreach ($faixas as $faixa) {

    $disco = $faixa['disco'] ?? 1;

    if (!isset($faixas_por_disco[$disco])) {
        $faixas_por_disco[$disco] = [];
    }

    $faixas_por_disco[$disco][] = $faixa;
}

// ==========================
//3️⃣ CALCULAR PROGRESSO DO ÁLBUM
// ==========================

$progresso_percent = calcularProgressoAlbum($pdo, $usuario_id, $album_id);

// ==========================
// LINKS DE STREAMING
// ==========================

$streamingLinks = buscarLinksStreaming($pdo, $album_id);


?>

<?php require __DIR__ . '/../app/includes/header.php';?>

    <script>
    const BASE_URL = "/";
    const CSRF_TOKEN = "<?= gerarCSRFToken() ?>";
    </script>

<div class="main-content">

    <div class="album-container" data-album-id="<?php echo (int)$album_id; ?>">

        <div class="album-left">

            <div class="album-header">

                <div class="album-header-top">

                    <div class="album-titles">
                        <div class="album-band">
                            <?php echo htmlspecialchars($album['banda_nome'] ?? ''); ?>
                        </div>

                        <div class="album-name">
                            <?php echo htmlspecialchars($album['titulo'] ?? ''); ?>
                        </div>

                        <div class="album-meta">
                            <span class="album-year">
                                <?php echo htmlspecialchars($album['ano'] ?? ''); ?>
                            </span>

                            <?php if ($estaNaDash): ?>
                                <form method="POST" action="<?php echo BASE_URL; ?>actions/remover_dash.php">
                                    <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                                    <?= csrfField() ?>
                                    <button type="submit" class="dash-btn dash-remove">
                                        Dash-
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?php echo BASE_URL; ?>actions/adicionar_dash.php">
                                    <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                                    <?= csrfField() ?>
                                    <button type="submit" class="dash-btn">
                                        Dash+
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="album-progress">
                        <div class="album-progress-bar">
                            <div class="album-progress-fill"
                                 style="width: <?php echo (int)$progresso_percent; ?>%;">
                            </div>
                        </div>
                        <div class="album-progress-text">
                            <?php echo $progresso_percent; ?>%
                        </div>
                    </div>

                </div>

            </div>

            <!-- TRACK LIST -->
            <div class="track-list">

                <?php foreach ($faixas_por_disco as $disco => $lista_faixas): ?>

                    <?php if (count($faixas_por_disco) > 1): ?>
                        <div class="disc-title">
                            Disc <?php echo $disco; ?>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($lista_faixas as $faixa): ?>


                        <div class="track-row">

                            <input type="hidden" name="faixa_id" value="<?php echo $faixa['id']; ?>">


                        <div class="track-number">
                            <?php echo $faixa['numero']; ?>
                        </div>

                        <div class="track-name">
                            <?php echo htmlspecialchars($faixa['nome'] ?? ''); ?>
                        </div>

                        <div class="track-duration">
                            <?php echo htmlspecialchars($faixa['duracao'] ?? ''); ?>
                        </div>

                        <div class="track-actions">
                            

                            <button type="button" class="play-btn">▶</button>
                            <button type="button" class="back-btn">−</button>

                            <span class="play-count">
                                <?php echo $faixa['total_ouvidas']; ?>
                            </span>

                            

                            
                            <!-- ESTRELAS -->
                            <form method="POST" action="salvar_avaliacao.php" class="ajax-faixa">
                                <input type="hidden" name="faixa_id" value="<?php echo $faixa['id']; ?>">

                                <div class="star-rating" data-nota="<?php echo $faixa['nota'] ?? 0; ?>">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star" data-value="<?php echo $i; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            </form>

                            <!-- FAVORITO -->

                            <span 
                                class="heart-btn<?php echo $faixa['favorita'] ? ' active' : ''; ?>"
                                data-faixa-id="<?php echo $faixa['id']; ?>"
                                >❤
                            </span>

                        </div>

                    </div>

                    <?php endforeach; ?>

                <?php endforeach; ?>


            </div>

        </div>

        <!-- CAPA -->
    <div class="album-cover">

        <?php $src = capaUrl(
            $album['mbid'] ?? null,
            $album['capa'] ?? null
            );
        ?>
        
        <img
            src="<?= htmlspecialchars($src) ?>"
            loading="lazy"
            onerror="this.src='/uploads/capas/default.jpg'"
        >

                <?php if (!empty($streamingLinks)): ?>

                <div class="album-streaming">

                    <?php foreach ($streamingLinks as $link): ?>

                        <a
                            href="<?= htmlspecialchars($link['url']) ?>"
                            target="_blank"
                            class="streaming-btn streaming-<?= htmlspecialchars($link['slug']) ?>"
                        >
                            Ouça no <?= htmlspecialchars($link['nome']) ?>
                        </a>

                    <?php endforeach; ?>

                </div>

                <?php endif; ?>




    <?php if (!empty($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
        <div style="margin-top:12px; text-align:left;" >
            <a href="/admin/nova_faixa.php?album_id=<?php echo $album['id']; ?>" 
               class="dash-btn">
                Editar
            </a>
        </div>
    <?php endif; ?>

</div>


</div>

<?php require __DIR__ . '/../app/includes/footer.php'; ?>


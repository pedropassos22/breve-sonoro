<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "includes/config.php";
require "includes/session.php";

verificarLogin();

if (!isset($_GET['id'])) {
    exit("Álbum não especificado.");
}

$album_id = $_GET['id'];

// ==========================
// 1️⃣ BUSCAR O ÁLBUM
// ==========================
$stmt = $pdo->prepare("
    SELECT albuns.*, bandas.nome AS banda_nome
    FROM albuns
    JOIN bandas ON albuns.banda_id = bandas.id
    WHERE albuns.id = ?
");

$stmt->execute([$album_id]);
$album = $stmt->fetch(PDO::FETCH_ASSOC);

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

$stmt = $pdo->prepare("
SELECT 
        f.id,
        f.album_id,
        f.disco,
        f.numero,
        f.nome,
        f.duracao,
        COUNT(r.id) AS total_ouvidas,
        a.nota,
        a.favorita

    FROM faixas f

    LEFT JOIN reproducoes r
        ON f.id = r.faixa_id
        AND r.usuario_id = ?

    LEFT JOIN avaliacoes a
        ON f.id = a.faixa_id
        AND a.usuario_id = ?

    WHERE f.album_id = ?

    GROUP BY f.id

    ORDER BY f.disco ASC, f.numero ASC
");

$stmt->execute([$usuario_id, $usuario_id, $album_id]);

$faixas = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
// 3️⃣ BUSCAR PROGRESSO SALVO
// ==========================

$stmtProg = $pdo->prepare("
    SELECT progresso 
    FROM progresso_album
    WHERE usuario_id = ? AND album_id = ?
");

$stmtProg->execute([$usuario_id, $album_id]);

$result = $stmtProg->fetch(PDO::FETCH_ASSOC);

$progresso_percent = $result['progresso'] ?? 0;



?>

<?php include 'includes/header.php'; ?>

<div class="main-content">

    <div class="album-container" data-album-id="<?php echo $album_id; ?>">

        <div class="album-left">

            <div class="album-header">

                <div class="album-header-top">

                    <div class="album-titles">
                        <div class="album-band">
                            <?php echo htmlspecialchars($album['banda_nome']); ?>
                        </div>

                        <div class="album-name">
                            <?php echo htmlspecialchars($album['titulo']); ?>
                        </div>

                        <div class="album-meta">
                            <span class="album-year">
                                <?php echo htmlspecialchars($album['ano']); ?>
                            </span>

                            <?php if ($estaNaDash): ?>
                                <form method="POST" action="remover_dash.php">
                                    <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" class="dash-btn dash-remove">
                                        Dash-
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="adicionar_dash.php">
                                    <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
                                 style="width: <?php echo $progresso_percent; ?>%;">
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
                            <?php echo htmlspecialchars($faixa['nome']); ?>
                        </div>

                        <div class="track-duration">
                            <?php echo htmlspecialchars($faixa['duracao']); ?>
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
                                class="heart-btn<?php echo $faixa['favorita'] ? ' active' : ''; ?><?php echo empty($faixa['nota']) ? ' disabled' : ''; ?>"
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

    <img src="uploads/capas/<?php echo htmlspecialchars($album['capa']); ?>">

    <?php if (!empty($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
        <div style="margin-top:12px; text-align:left;" >
            <a href="nova_faixa.php?album_id=<?php echo $album['id']; ?>" 
               class="dash-btn">
                Editar
            </a>
        </div>
    <?php endif; ?>

</div>


</div>

<?php include 'includes/footer.php'; ?>


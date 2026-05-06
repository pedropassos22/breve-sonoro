<?php
require __DIR__ . '/../app/includes/bootstrap.php';

verificarLogin();

// Busca todos os álbuns cadastrados
$usuario_id = $_SESSION['usuario_id'];

$limite = 18;

// Página atual
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1;


if ($pagina < 1) {
    $pagina = 1;
}

$offset = ($pagina - 1) * $limite;

$totalStmt = $pdo->query("
SELECT COUNT(*)
FROM albuns a
INNER JOIN bandas b ON a.banda_id = b.id
");

$totalAlbuns = (int) $totalStmt->fetchColumn();

$totalPaginas = max(1, ceil($totalAlbuns / $limite));




$stmt = $pdo->prepare("
SELECT
    a.id,
    a.titulo,
    a.ano,
    a.mbid,
    a.capa,
    b.nome AS banda_nome


FROM albuns a
INNER JOIN bandas b ON a.banda_id = b.id

ORDER BY a.id DESC
LIMIT :limite OFFSET :offset
");

$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

$albuns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($albuns as $key => $album) {
    $albuns[$key]['progresso'] =
        calcularProgressoAlbum($pdo, $usuario_id, $album['id']);
}

?>


<?php require __DIR__ . '/../app/includes/header.php'; ?>



<h2 class="section-title">Recentes</h2>


<?php if (count($albuns) > 0): ?>

<div class="album-grid">

<?php foreach ($albuns as $album): ?>

<div class="album-card">

    <a href="album.php?id=<?php echo (int)$album['id']; ?>">
        <img
            <?php
            $src = capaUrl(
                $album['mbid'] ?? null,
                $album['capa'] ?? null
            );
            ?>

            <img
                src="<?= htmlspecialchars($src) ?>"
                loading="lazy"
                onerror="this.src='/uploads/capas/default.jpg'"
            >  
    </a>

<div class="album-info">

    <div class="album-title">
        <?php echo htmlspecialchars($album['titulo']); ?>
    </div>

    <div class="album-year-card">
        (<?php echo htmlspecialchars($album['ano'] ?? ''); ?>)
    </div>

    <div class="album-artist">
        <?php echo htmlspecialchars($album['banda_nome']); ?>
    </div>


</div>


    <div class="card-progress-container">
    <div class="card-progress-bar">
        <div class="card-progress-fill" 
             style="width: <?php echo (int)$album['progresso']; ?>%;">
        </div>
    </div>
    <div class="card-progress-text">
        <?php echo (int)$album['progresso']; ?>%
    </div>
</div>

</div>

<?php endforeach; ?>

</div>



<?php else: ?>
    <p>Nenhum álbum cadastrado ainda.</p>
<?php endif; ?>

<br>

<?php if ($totalPaginas > 1): ?>

<div class="pagination">


    <?php
    $maxLinks = 5; // quantos números mostrar ao redor da página atual
    $inicio = max(1, $pagina - 2);
    $fim = min($totalPaginas, $pagina + 2);
    ?>

    <!-- BOTÃO ANTERIOR -->
    <?php if ($pagina > 1): ?>
        <a href="index.php?pagina=<?php echo $pagina - 1; ?>" style="margin:0 8px;">
            Anterior
        </a>
    <?php endif; ?>

    <!-- PRIMEIRA PÁGINA -->
    <?php if ($inicio > 1): ?>
        <a href="index.php?pagina=1" style="margin:0 8px;">1</a>
        <?php if ($inicio > 2): ?>
            ...
        <?php endif; ?>
    <?php endif; ?>

    <!-- PÁGINAS DO MEIO -->
    <?php for ($i = $inicio; $i <= $fim; $i++): ?>
        <?php if ($i == $pagina): ?>
            <strong style="margin:0 8px;"><?php echo $i; ?></strong>
        <?php else: ?>
            <a href="index.php?pagina=<?php echo $i; ?>" style="margin:0 8px;">
                <?php echo $i; ?>
            </a>
        <?php endif; ?>
    <?php endfor; ?>

    <!-- ÚLTIMA PÁGINA -->
    <?php if ($fim < $totalPaginas): ?>
        <?php if ($fim < $totalPaginas - 1): ?>
            ...
        <?php endif; ?>
        <a href="index.php?pagina=<?php echo $totalPaginas; ?>" style="margin:0 8px;">
            <?php echo $totalPaginas; ?>
        </a>
    <?php endif; ?>

    <!-- BOTÃO PRÓXIMA -->
    <?php if ($pagina < $totalPaginas): ?>
        <a href="index.php?pagina=<?php echo $pagina + 1; ?>" style="margin:0 8px;">
            Próxima
        </a>
    <?php endif; ?>

</div>

<?php endif; ?>
<?php require __DIR__ . '/../app/includes/footer.php'; ?>

<?php
require "includes/config.php";
require "includes/session.php";

if (!isset($_SESSION['usuario_id'])) {
    die("Acesso negado.");
}



verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("
SELECT 
    a.id,
    a.titulo,
    a.ano,
    a.capa,
    b.nome AS banda_nome,

ROUND(
(
    SUM(
        CASE 
            WHEN EXISTS (
                SELECT 1
                FROM reproducoes r
                WHERE r.faixa_id = f.id
                AND r.usuario_id = ?
                LIMIT 1
            )
            THEN 0.5 ELSE 0
        END
        +
        CASE 
            WHEN av.nota IS NOT NULL THEN 0.5
            ELSE 0
        END
    )
    / COUNT(f.id)
) * 100
) AS progresso


FROM usuario_dash d
JOIN albuns a ON d.album_id = a.id
JOIN bandas b ON a.banda_id = b.id
JOIN faixas f ON f.album_id = a.id

LEFT JOIN avaliacoes av
    ON av.faixa_id = f.id
    AND av.usuario_id = ?


WHERE d.usuario_id = ?

GROUP BY a.id
ORDER BY a.id DESC
");


$stmt->execute([
    $usuario_id, // EXISTS reproducoes
    $usuario_id, // avaliacoes
    $usuario_id  // dash
]);


$albuns = $stmt->fetchAll(PDO::FETCH_ASSOC);

$paginaClasse = 'dash';
?>

<?php include 'includes/header.php'; ?>

<h2 class="section-title">Minha Dash</h2>

<?php if (count($albuns) > 0): ?>

<div class="album-grid">

<?php foreach ($albuns as $album): ?>

<div class="album-card">

    <a href="album.php?id=<?php echo $album['id']; ?>">
        <img 
        src="uploads/capas/<?php echo htmlspecialchars($album['capa'] ?: 'default.jpg'); ?>"
        loading="lazy"
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
    <p>Você ainda não adicionou nenhum álbum à sua Dash.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>


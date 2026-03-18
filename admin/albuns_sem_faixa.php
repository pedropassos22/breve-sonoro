<?php
require "../includes/config.php";
require "../includes/session.php";

verificarLogin();
verificarAdmin();

$stmt = $pdo->query("
    SELECT 
        a.id,
        a.titulo,
        a.ano,
        a.capa,
        b.nome AS banda_nome
    FROM albuns a
    INNER JOIN bandas b ON a.banda_id = b.id
    LEFT JOIN faixas f ON f.album_id = a.id
    WHERE f.id IS NULL
    ORDER BY a.id DESC
");

$albuns = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Álbuns sem Faixas - breve-sonoro</title>
</head>
<body>

<h2>Álbuns sem faixas cadastradas</h2>

<p><a href="admin.php">Voltar ao Painel</a></p>

<hr>

<?php if (count($albuns) > 0): ?>

<div style="display:flex; flex-wrap:wrap; gap:30px;">

<?php foreach ($albuns as $album): ?>

<div style="width:220px; text-align:center;">

    <a href="../album.php?id=<?php echo $album['id']; ?>">
       <img 
            src="../uploads/capas/<?php echo htmlspecialchars($album['capa']); ?>"
            style="width:220px; height:220px; object-fit:cover; border-radius:10px;">
    </a>

    <div style="margin-top:10px;">
        <strong><?php echo htmlspecialchars($album['titulo']); ?></strong><br>
        <?php echo htmlspecialchars($album['banda_nome']); ?><br>
        (<?php echo htmlspecialchars($album['ano'] ?? ''); ?>)
    </div>

    <div style="margin-top:8px;">
        <a href="../nova_faixa.php?album_id=<?php echo $album['id']; ?>">
            ➕ Cadastrar Faixa
        </a>
    </div>

</div>

<?php endforeach; ?>

</div>

<?php else: ?>

    <p style="color:green;">Todos os álbuns possuem faixas 🎉</p>

<?php endif; ?>

<br>
<a href="../logout.php">Sair</a>

</body>
</html>

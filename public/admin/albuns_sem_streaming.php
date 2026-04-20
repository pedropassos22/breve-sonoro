<?php
require dirname(__DIR__, 2) . '/app/includes/bootstrap.php';

verificarLogin();
verificarAdmin();

$plataforma = $_GET['plataforma'] ?? '';

if (!$plataforma) {
    die('Plataforma inválida.');
}

$stmt = $pdo->prepare("
    SELECT a.id, a.titulo, b.nome AS banda, a.ano
    FROM albuns a
    JOIN bandas b ON b.id = a.banda_id

    LEFT JOIN plataformas_streaming p
        ON p.slug = ?

    LEFT JOIN album_streaming_links s
        ON s.album_id = a.id
        AND s.plataforma_id = p.id

    WHERE s.id IS NULL
    ORDER BY b.nome, a.titulo
");


$stmt->execute([$plataforma]);

$albuns = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Álbuns sem <?php echo htmlspecialchars($plataforma); ?></title>
</head>

<body>

<h2>Álbuns sem link: <?php echo htmlspecialchars($plataforma); ?></h2>

<p>
    <a href="/admin/admin.php">← Voltar ao Admin</a>
</p>

<table border="1" cellpadding="8">
<tr>
    <th>Banda</th>
    <th>Álbum</th>
    <th>Ano</th>
    <th>Abrir</th>
</tr>

<?php foreach ($albuns as $album): ?>
<tr>
    <td><?= htmlspecialchars($album['banda']) ?></td>
    <td><?= htmlspecialchars($album['titulo']) ?></td>
    <td><?= $album['ano'] ?></td>
    <td>
        <a href="/admin/nova_faixa.php?album_id=<?= $album['id'] ?>">
            Abrir Álbum
        </a>
    </td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>

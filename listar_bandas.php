<?php
require "includes/config.php";
require "includes/session.php";

verificarLogin();
verificarAdmin();

// Busca todas as bandas
$stmt = $pdo->query("
    SELECT 
        b.id,
        b.nome,
        b.ano_formacao,
        b.cidade,
        GROUP_CONCAT(g.nome ORDER BY g.nome SEPARATOR ', ') AS generos
    FROM bandas b
    LEFT JOIN banda_genero bg ON b.id = bg.banda_id
    LEFT JOIN generos g ON bg.genero_id = g.id
    GROUP BY b.id
    ORDER BY b.nome ASC
");


$bandas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Listar Bandas - breve-sonoro</title>
</head>
<body>

<h2>Bandas Cadastradas</h2>

<?php if (count($bandas) > 0): ?>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Nome</th>
        <th>Ano de Formação</th>
        <th>Cidade</th>
        <th>Gêneros</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($bandas as $banda): ?>
        <tr>
            <td><?php echo htmlspecialchars($banda['nome']); ?></td>
            <td><?php echo htmlspecialchars($banda['ano_formacao'] ?? '-'); ?></td>
            <td><?php echo htmlspecialchars($banda['cidade'] ?? '-'); ?></td>
            <td><?php echo htmlspecialchars($banda['generos'] ?? '-'); ?></td>
            
            <td>
                <a href="editar_banda.php?id=<?php echo $banda['id']; ?>">
                    Editar
                </a>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<?php else: ?>

<p>Nenhuma banda cadastrada.</p>

<?php endif; ?>

<br>
<a href="admin/admin.php">Voltar</a><br>
<a href="nova_banda.php">Cadastrar nova banda</a>

</body>
</html>

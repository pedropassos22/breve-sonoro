<?php
require dirname(__DIR__, 2) . '/app/includes/bootstrap.php';

verificarLogin();
verificarAdmin();


// TOTAL USUÁRIOS
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
        $totalUsuarios = $stmt->fetchColumn();

        // TOTAL BANDAS
        $stmt = $pdo->query("SELECT COUNT(*) FROM bandas");
        $totalBandas = $stmt->fetchColumn();

        // TOTAL ALBUNS
        $stmt = $pdo->query("SELECT COUNT(*) FROM albuns");
        $totalAlbuns = $stmt->fetchColumn();

        // TOTAL FAIXAS
        $stmt = $pdo->query("SELECT COUNT(*) FROM faixas");
        $totalFaixas = $stmt->fetchColumn();

        // ALBUNS SEM FAIXAS
        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM albuns a
            LEFT JOIN faixas f ON f.album_id = a.id
            WHERE f.id IS NULL
        ");
        $totalAlbunsSemFaixa = $stmt->fetchColumn();

        // ÁLBUNS SEM SPOTIFY
        $stmt = $pdo->query("
            SELECT COUNT(*)
            FROM albuns a
            LEFT JOIN plataformas_streaming p 
                ON p.slug = 'spotify'
            LEFT JOIN album_streaming_links s
                ON s.album_id = a.id
                AND s.plataforma_id = p.id
            WHERE s.id IS NULL
        ");
        $totalSemSpotify = $stmt->fetchColumn();


        // ÁLBUNS SEM QOBUZ
        $stmt = $pdo->query("
            SELECT COUNT(*)
            FROM albuns a
            LEFT JOIN plataformas_streaming p 
                ON p.slug = 'qobuz'
            LEFT JOIN album_streaming_links s
                ON s.album_id = a.id
                AND s.plataforma_id = p.id
            WHERE s.id IS NULL
        ");
        $totalSemQobuz = $stmt->fetchColumn();



        // ÚLTIMOS 5 ÁLBUNS
        $stmt = $pdo->query("
            SELECT a.id, a.titulo, b.nome AS banda, a.ano
            FROM albuns a
            JOIN bandas b ON b.id = a.banda_id
            ORDER BY a.id DESC
            LIMIT 5
        ");
        $ultimosAlbuns = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .card {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .card h3 { margin: 0 0 10px 0; }
        .numero { font-size: 28px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>

<h2>Dashboard Administrativo</h2>

<p>
    <a href="/index.php">Início</a> |
    <a href="/dash.php">Minha Dash</a> |
    <a href="/logout.php">Sair</a>
</p>

<hr>


<div class="grid">
    <div class="card">
        <h3>Usuários</h3>
        <div class="numero"><?php echo $totalUsuarios; ?></div>
    </div>

    <div class="card">
        <h3>Bandas</h3>
        <div class="numero"><?php echo $totalBandas; ?></div>
    </div>

    <div class="card">
        <h3>Álbuns</h3>
        <div class="numero"><?php echo $totalAlbuns; ?></div>
    </div>

    <div class="card">
        <h3>Faixas</h3>
        <div class="numero"><?php echo $totalFaixas; ?></div>
    </div>

    <div class="card">
        <h3>Álbuns sem Faixa</h3>
        <div class="numero"><?php echo $totalAlbunsSemFaixa; ?></div>
    </div>
    
    <div class="card">
    <h3>Álbuns sem Spotify</h3>
    <div class="numero">
        <a href="/admin/albuns_sem_streaming.php?plataforma=spotify">
            <?php echo $totalSemSpotify; ?>
        </a>
    </div>
    </div>

    <div class="card">
        <h3>Álbuns sem Qobuz</h3>
        <div class="numero">
            <a href="/admin/albuns_sem_streaming.php?plataforma=qobuz">
                <?php echo $totalSemQobuz; ?>
            </a>
        </div>
    </div>

</div>

<h3>Últimos 5 Álbuns Cadastrados</h3>

<table>
    <tr>
        <th>Título</th>
        <th>Banda</th>
        <th>Ano</th>
        <th>Ação</th>
    </tr>

    <?php foreach ($ultimosAlbuns as $album): ?>
        <tr>
            <td><?php echo htmlspecialchars($album['titulo']); ?></td>
            <td><?php echo htmlspecialchars($album['banda']); ?></td>
            <td><?php echo $album['ano']; ?></td>
            <td>
                <form method="POST" action="excluir_album.php" onsubmit="return confirm('Tem certeza que deseja excluir este álbum?');">
                    <input type="hidden" name="album_id" value="<?php echo $album['id']; ?>">
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                    <button type="submit">Excluir</button>
                </form>
            </td>

        </tr>
    <?php endforeach; ?>
</table>

<hr>

<h3>Gerenciamento</h3>

<ul>
    <li><a href="/admin/novo_album.php">Cadastrar novo álbum</a></li>
    <li><a href="/admin/nova_banda.php">Cadastrar nova banda</a></li>
    <li><a href="/admin/nova_genero.php">Cadastrar gênero</a></li>
    <li><a href="/admin/listar_bandas.php">Listar bandas</a></li>
    <li><a href="/admin/albuns_sem_faixa.php">Álbuns sem faixas</a></li>
</ul>

<hr>

<a href="/index.php">Voltar ao início</a>

</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>breve-sonoro</title>
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>


<body class="<?php echo $paginaClasse ?? ''; ?>">


<header class="topbar">
    <div class="topbar-container">

        <div class="logo">
            <?php echo $_SESSION['usuario_nome'] ?? 'BREVE-SONORO'; ?>
        </div>

        <nav class="menu">
            <a href="index.php">Início</a>
            <?php if(isset($_SESSION['usuario_id'])): ?>
                <a href="dash.php">Minha Dash</a>
            <?php endif; ?>

            <?php if(isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                <a href="admin/admin.php">Painel Administrativo</a>
            <?php endif; ?>
        </nav>

        <div class="logout">
            <?php if(isset($_SESSION['usuario_id'])): ?>
                <a href="logout.php">Sair</a>
            <?php endif; ?>
        </div>

    </div>
</header>

<main class="main-content">

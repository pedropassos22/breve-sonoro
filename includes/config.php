<?php

define('BASE_URL', '/breve-sonoro/');

// Define as configurações de conexão com o banco

$host = "localhost"; 
// Servidor do banco (localhost porque está rodando no seu PC)

$db   = "brevesonoro"; 
// Nome do banco de dados que você criou no phpMyAdmin

$user = "rooot"; 
// Usuário padrão do MySQL no XAMPP

$pass = ""; 
// Senha do MySQL (no XAMPP padrão geralmente é vazia)

$charset = "utf8mb4"; 
// Charset moderno que suporta acentos e caracteres especiais corretamente


// Cria a string de conexão (DSN - Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";


// Define opções importantes da conexão
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Faz o PHP lançar erro se algo der errado (melhor para desenvolvimento)

    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Quando buscar dados, retorna como array associativo (mais organizado)

    PDO::ATTR_EMULATE_PREPARES   => false,
    // Usa prepared statements reais (mais seguro)
];


// Tenta criar a conexão
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Se funcionar, a variável $pdo estará pronta para uso no sistema
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Se falhar, exibe erro e para tudo
    exit("Erro na conexão com o banco: " . $e->getMessage());
}

<?php


/*
|--------------------------------------------------------------------------
| BASE URL
|--------------------------------------------------------------------------
*/

define('BASE_URL', '/');



/*
|--------------------------------------------------------------------------
| Conexão PDO
|--------------------------------------------------------------------------
*/

$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {

    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {

    error_log('Erro de conexão: ' . $e->getMessage());

    if (($_ENV['APP_ENV'] ?? '') === 'local') {
        exit('Erro de conexão com banco: ' . $e->getMessage());
    }

    exit('Erro de conexão com banco.');
}

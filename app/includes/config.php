<?php

/*
|--------------------------------------------------------------------------
| Carregar .env
|--------------------------------------------------------------------------
*/

$envPath = dirname(__DIR__, 2) . '/.env';

if (file_exists($envPath)) {

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);

        $_ENV[trim($key)] = trim($value);
    }
}


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

    exit('Erro de conexão com banco.');
}

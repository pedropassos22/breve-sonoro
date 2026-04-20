    <?php
    
    /**
     * ======================================
     * BREVE-SONORO BOOTSTRAP
     * Núcleo da aplicação
     * ======================================
     */

    if (!defined('APP_START')) {
        define('APP_START', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Paths da aplicação
    |--------------------------------------------------------------------------
    */

    defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__, 2));
    defined('APP_PATH') or define('APP_PATH', BASE_PATH . '/app');
    defined('PUBLIC_PATH') or define('PUBLIC_PATH', BASE_PATH . '/public');
    defined('STORAGE_PATH') or define('STORAGE_PATH', BASE_PATH . '/storage');


    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    */
    date_default_timezone_set('America/Sao_Paulo');

    /*
    |--------------------------------------------------------------------------
    | Encoding
    |--------------------------------------------------------------------------
    */
    mb_internal_encoding('UTF-8');

    /*
    |--------------------------------------------------------------------------
    | Environment (.env)
    |--------------------------------------------------------------------------
    */

    $envPath = dirname(__DIR__, 2) . '/.env';

    if (file_exists($envPath)) {

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {

            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
            continue;
            }

            [$name, $value] = explode('=', $line, 2);


            $_ENV[trim($name)] = trim($value);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    */

    $env = $_ENV['APP_ENV'] ?? 'production';

    if ($env === 'local') {

        ini_set('display_errors', 1);
        error_reporting(E_ALL);

    } else {

        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        ini_set('error_log', dirname(__DIR__, 2) . '/storage/logs/php_errors.log');
    }

    /*
    |--------------------------------------------------------------------------
    | Core Includes
    |--------------------------------------------------------------------------
    */

    require_once __DIR__ . "/session.php";
    require_once __DIR__ . '/csrf.php';
    require_once __DIR__ . "/config.php";
    require_once __DIR__ . "/action_helper.php";
    require_once __DIR__ . '/musicbrainz.php';
    require_once __DIR__ . '/../services/AlbumStreamingService.php';
    require_once __DIR__ . '/../services/AdminStreamingService.php';

    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    */

    require_once __DIR__ . "/../services/albumService.php";

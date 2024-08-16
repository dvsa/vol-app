<?php
// phpcs:ignoreFile

declare(strict_types=1);

use Laminas\Mvc\Application;

$startTime = microtime(true);

error_reporting(E_ALL & ~E_USER_DEPRECATED);

chdir(dirname(__DIR__));

date_default_timezone_set('UTC');

// Ensures at the very least we send a 500 response on fatal
register_shutdown_function('handleFatal');
function handleFatal()
{
    $error = error_get_last();
    if ($error) {
        http_response_code(500);

        if (ob_get_length() !== false) {
            ob_clean();
        }

        echo json_encode(
            [
                'messages' => [
                    'An unexpected fatal error occurred' => [
                        $error['message'],
                        $error['file'] . ': ' . $error['line']
                    ]
                ]
            ]
        );
        exit;
    }
}

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (is_string($path) && __FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

include __DIR__ . '/../vendor/autoload.php';

if (!class_exists(Application::class)) {
    throw new RuntimeException(
        "Unable to load application.\n"
        . "- Type `composer install` if you are developing locally.\n"
        . "- Type `docker-compose run laminas composer install` if you are using Docker.\n"
    );
}

$container = require __DIR__ . '/../config/container.php';

// Run the application!
$container->get('Application')->run();

$time = round(microtime(true) - $startTime, 5);
\Olcs\Logging\Log\Logger::debug(
    'Backend complete',
    [
        'time' => (string)$time,
        'url' => $_SERVER['REQUEST_URI'],
        'peak-memory-usage-MB' => (int)(memory_get_peak_usage() / 1024 / 1024)
    ]
);

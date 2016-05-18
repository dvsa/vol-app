<?php
$startTime = microtime(true);

$profile = getenv("XHPROF_ENABLE") == 1;

if ($profile) {
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

error_reporting(-1);
ini_set("display_errors", 1);
ini_set('intl.default_locale', 'en_GB');
date_default_timezone_set('Europe/London');

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();

if ($profile) {
    $xhprof_data = xhprof_disable();

    require_once "/workspace/xhprof/xhprof_lib/utils/xhprof_lib.php";
    require_once "/workspace/xhprof/xhprof_lib/utils/xhprof_runs.php";

    $xhprof_runs = new XHProfRuns_Default();

    $run_id = $xhprof_runs->save_run($xhprof_data, "olcs-selfserve");

    $fp = fopen("/tmp/xhprof.log", "a");

    $uri = strtok($_SERVER['REQUEST_URI'], "?");
    $request = $_SERVER['REQUEST_METHOD'] . " " . $uri;

    $content = "[olcs-selfserve] " . date("Y-m-d H:i:s") . " " . $request
        . " http://192.168.149.2/xhprof/xhprof_html/index.php?run=" . $run_id . "&source=olcs-selfserve\n";

    fwrite($fp, $content);
    fclose($fp);
}

$time = round(microtime(true) - $startTime, 5);
\Olcs\Logging\Log\Logger::debug('Selfserve complete', ['time' => $time, 'url' => $_SERVER['REQUEST_URI']]);

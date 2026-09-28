<?php

    declare(strict_types=1);

    chdir(dirname(__DIR__));

    include __DIR__ . '/../vendor/autoload.php';

    $container = require __DIR__ . '/../config/container.php';
    $config = $container->get('Config');

    $config = $config['govukaccount-redirect'];
    $referer = $_SERVER['HTTP_REFERER'] ?? null;

    // Remove any GET Params not allowed in the config whitelist, then make the redirect string for a 302 or meta refresh.
    $sanitizedQueryString = http_build_query(array_intersect_key($_GET, array_flip($config['get-whitelist'])));
    $redirectString = $config['redirect-path'] . '?' . $sanitizedQueryString;

    // Check referrer ends with specified string(s) from config, otherwise goto /
    $refererHost = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST);
    if (!empty($refererHost)) {
        $allowed = false;
        $allowedEndings = is_array($config['referrer_ends_with']) ? $config['referrer_ends_with'] : [$config['referrer_ends_with']];
        
        foreach ($allowedEndings as $ending) {
            $needleLength = strlen($ending);
            if (0 === substr_compare($refererHost, $ending, -$needleLength)) {
                $allowed = true;
                break;
            }
        }
        
        if (!$allowed) {
            header('Location: /', true, 302);
            exit;
        }
    }

    header('Location: ' . $redirectString, true, 302);
    exit;
?>


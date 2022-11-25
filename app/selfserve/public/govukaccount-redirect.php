<?php

    declare(strict_types=1);

    $config = require '../config/autoload/local.php';
    $config = $config['govukaccount-redirect'];
    $referer = $_SERVER['HTTP_REFERER'] ?? null;

    // Remove any GET Params not allowed in the config whitelist, then make the redirect string for a 302 or meta refresh.
    $sanitizedQueryString = http_build_query(array_intersect_key($_GET, array_flip($config['get-whitelist'])));
    $redirectString = $config['redirect-path'] . '?' . $sanitizedQueryString;

    // Check referrer ends with specified string from config, otherwise goto /
    $needleLength = strlen($config['referrer_ends_with']);
    $refererHost = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST);
    if (!empty($refererHost) && !(0 === substr_compare($refererHost, $config['referrer_ends_with'], -$needleLength))) {
        header('Location: /', true, 302);
        exit;
    }

    header('Location: ' . $redirectString, true, 302);
    exit;
?>


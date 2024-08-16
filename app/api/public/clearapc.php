<?php

// Clear APCu cache (assumes context is Apache and PHP 5.5)
if (function_exists('apcu_clear_cache')) {
    if (apcu_clear_cache()) {
        echo 'APCu cache cleared ';
    } else {
        echo 'APCu cache clear FAILED ';
    }
} else {
    echo 'APCu clear cache function not available ';
}

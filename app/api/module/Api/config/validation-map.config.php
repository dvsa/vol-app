<?php

$map = [];

// Merge all other validation maps
foreach (glob(__DIR__ . '/validation-map/*.config.php') as $filename) {
    $map += include($filename);
}

return $map;

<?php

$applicationConfig = require __DIR__ .'/application.config.php';

// push Cli module onto the stack
$applicationConfig['modules'][] = 'Cli';

return $applicationConfig;

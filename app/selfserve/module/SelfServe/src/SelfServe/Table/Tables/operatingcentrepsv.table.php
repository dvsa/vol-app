<?php

$operatingCentreConfig = include(dirname(__FILE__) . '/operatingcentre.table.php');

//reconfigure
unset($operatingCentreConfig['columns']['trailersCol']);
$operatingCentreConfig['footer'][0]['format'] = 'Total vehicles';
unset($operatingCentreConfig['footer'][2]);

return $operatingCentreConfig;
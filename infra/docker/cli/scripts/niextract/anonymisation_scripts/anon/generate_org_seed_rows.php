<?php

require_once '../../vendor/autoload.php';

if ($argc !== 3) {
    echo "Please specify two arguments: The org table id to start from, and how many lines to generate";
    echo "{$argv[0]} [orgTableId] [numberOfRows]\n\n";
    echo "e.g:\n        {$argv[0]} 1000001 100\n\n";
    exit(1);
}

if (!is_numeric($argv[1]) || !is_numeric($argv[2]))  {
    echo "Arguments must be numbers!\n\n";
    exit(1);
} else {
    $startId = $argv[1];
    $numLines = $argv[2];
}

$faker = Faker\Factory::create('en_GB');
$sqlLines = [];
$parts = ['INDUSTRIES', 'TRANSPORT', 'LOGISTICS', 'CONSTRUCTION', 'SECURITY', 'SOLUTIONS', 'CONSULTANCY', 'SERVICES', 'GROUP', 'ASSOCIATES'];
$suffixes = ['LTD', 'PLC', 'LLC', 'LIMITED'];
$i = 1;
while($i <= $numLines) {

    $sqlLine = 'UPDATE organisation SET name = "';
    $sqlLine .= strtoupper($faker->company);

    if($i %3 == 0){
        $sqlLine .=  " ".$parts[array_rand($parts)];
    }

    $sqlLine .=  " ".$suffixes[array_rand($suffixes)];

    $sqlLine .= '" WHERE id = '.$startId.";\n";

    if(!in_array($sqlLine, $sqlLines)){
        $sqlLines[$i] = str_replace(',', '', $sqlLine);
        $startId++;
        $i++;
    }
}

file_put_contents('./data/organisation_seed.sql', $sqlLines, FILE_APPEND);







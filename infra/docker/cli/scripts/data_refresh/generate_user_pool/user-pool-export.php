<?php

$defaultOptions = [
    'host' => getenv('USER_POOL_EX_HOST') ?: 'localhost',
    'user' => getenv('USER_POOL_EX_USER') ?: 'mysql',
    'password' => getenv('USER_POOL_EX_PASS') ?: 'olcs',
    'port' => getenv('USER_POOL_EX_PORT') ?: '3306',
    'database' => getenv('USER_POOL_EX_DATABASE') ?: 'olcs_be',
    'output' => getenv('USER_POOL_EX_OUTPUT') ?: 'stdout',
    'lastlogin' => getenv('USER_POOL_EX_LASTLOGIN') ?:  '',
    'mode' => getenv('USER_POOL_EX_MODE') ?:  'dr-export',
    'perrole' => getenv('USER_POOL_PER_ROLE') ?:  2,
    'append' => getenv('USER_POOL_APPEND') ?:  null,
    'separator' => getenv('USER_POOL_SEPARATOR') ?: '|',
];

$options = array_replace(
    $defaultOptions,
    getopt('', ['host::', 'user::', 'password::', 'database::', 'port::', 'output::', 'lastlogin::', 'help::', 'mycnf::', 'mode::', 'perrole::', 'append::', 'separator::']));

function helptext() {
    echo "User Pool CSV Export Script\n\n";
    echo "usage:\n\n";
    echo "  --separator             default: |\n";
    echo "  --mode                  Sets dr-export or nonprod-users operation mode\n";
    echo "  --fromenv               Gets configuration from env vars, ignoring any defaults (See below)\n";
    echo "  --mycnf={PATH}          Path to configuration ini file. Values here will override defaults\n";
    echo "  --host=DBHOSTNAME\n";
    echo "  --user=DBUSERNAME\n";
    echo "  --password=DBPASSWORD\n";
    echo "  --port=DBPORT           default: 3306\n";
    echo "  --database=DBNAME\n";
    echo "  --output={PATH|stdout}  default: stdout - specify full path or 'stdout'\n";
    echo "\n";
    echo "\n";
    echo "  --lastlogin=\"2010-01-01 10:10:01\"\n\n  for dr-export mode - export users who logged in after this date";
    echo "  --perrole=\"2\"\n\n  for nonprod-users mode - how many maxe to the above defaults, if set\n";
    echo "  --append=\"path\"\n\n  append specified file to output\n";
    echo "Any specific command line params specified will override config from the environment\n\n";
    echo "  USER_POOL_EX_HOST\n";
    echo "  USER_POOL_EX_USER\n";
    echo "  USER_POOL_EX_PASS\n";
    echo "  USER_POOL_EX_PORT\n";
    echo "  USER_POOL_EX_DATABASE\n";
    echo "  USER_POOL_EX_OUTPUT\n";
    echo "  USER_POOL_EX_MODE\n";
    echo "  USER_POOL_PER_ROLE\n";
    echo "  USER_POOL_SEPARATOR\n";
    echo "  USER_POOL_EX_LASTLOGIN\n\n";
    exit();
}

if(isset($options['mycnf'])) {
    $options = array_replace(
        $options,
        parse_ini_file($options['mycnf'],false)
    );
}

if ($argc === 1 || array_key_exists('help', $options)) {
    helptext();
}

include('UserPoolExport.php');
$generator = new UserPoolExport(
    $options['host'],
    $options['password'],
    $options['user'],
    $options['database'],
    $options['port'],
    $options['output'],
    $options['lastlogin'],
    $options['mode'],
    $options['separator']
);

switch ($options['mode']){
    case 'dr-export':
        $generator->exportToCsv($options['append']);
        break;
    case 'nonprod-users':
        $generator->getUsersPerRole($options['perrole'], $options['append']);
        break;
    default:
        helptext();
        break;
}
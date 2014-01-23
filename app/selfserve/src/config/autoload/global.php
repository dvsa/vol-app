<?php
return array(
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=test;host=localhost',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
                    => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            'VosaCommonEntities_Driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../../module/VosaCommonEntities/src/VosaCommonEntities/Entity'),
            ),
            'orm_default' => array(
                'drivers' => array(
                    'VosaCommonEntities\Entity' => 'VosaCommonEntities_Driver',
                ),
            ),
        ),
    ),
);

<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// Initialize VOL Application
chdir(__DIR__);
require 'vendor/autoload.php';

$app = \Laminas\Mvc\Application::init(require 'config/application.config.php');
$serviceManager = $app->getServiceManager();

/** @var EntityManager $entityManager */
$entityManager = $serviceManager->get(EntityManager::class);

return ConsoleRunner::createHelperSet($entityManager);

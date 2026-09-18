<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Support;

use Doctrine\ORM\EntityManagerInterface;
use Dvsa\Olcs\Api\Domain\QueryPartialServiceManager;
use Dvsa\Olcs\Api\Domain\QueryPartialServiceManagerFactory;
use Laminas\ServiceManager\ServiceManager;

/**
 * A real QueryPartialServiceManager for unit tests.
 *
 * Built from the application's own partial config rather than a restatement of it, so the
 * partials a test exercises are the ones production wires — the two cannot drift. The
 * container supplies only what the partial factories ask for: the EntityManager (WithRefdata
 * reads association metadata from it) and the manager itself (the factories resolve 'with'
 * through it).
 */
final class QueryPartials
{
    public static function serviceManager(EntityManagerInterface $em): QueryPartialServiceManager
    {
        $config = require DoctrineMetadata::appRoot() . '/module/Api/config/module.config.php';

        $container = new ServiceManager();
        $container->setService('doctrine.entitymanager.orm_default', $em);

        $manager = new QueryPartialServiceManager(
            $container,
            $config[QueryPartialServiceManagerFactory::CONFIG_KEY],
        );

        // Circular by design: WithRefdataFactory and friends resolve 'with' back through the
        // manager. Factories run on first get(), which is after this line.
        $container->setService('QueryPartialServiceManager', $manager);

        return $manager;
    }
}

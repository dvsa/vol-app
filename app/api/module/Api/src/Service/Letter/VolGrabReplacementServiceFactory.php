<?php

namespace Dvsa\Olcs\Api\Service\Letter;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BookmarkFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for VolGrabReplacementService
 */
class VolGrabReplacementServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container     Service container
     * @param string             $requestedName Requested service name
     * @param array|null         $options       Creation options
     *
     * @return VolGrabReplacementService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new VolGrabReplacementService(
            new BookmarkFactory(),
            $container->get('QueryHandlerManager'),
            $container->get('DateService'),
            $container->get('Translator')
        );
    }
}

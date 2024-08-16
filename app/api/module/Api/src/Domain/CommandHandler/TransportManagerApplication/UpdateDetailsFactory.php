<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\AcquiredRights\Service\AcquiredRightsService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class UpdateDetailsFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return UpdateDetails
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $acquiredServiceService = $container->get(AcquiredRightsService::class);
        $instance = new UpdateDetails($acquiredServiceService);
        return $instance->__invoke($container, $requestedName, $options);
    }
}

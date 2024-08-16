<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Logger;

use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Psr\Container\ContainerInterface;

/**
 * @see EntityAccessLogger
 * @see \Dvsa\OlcsTest\Api\Logger\EntityAccessLoggerFactoryTest
 */
class EntityAccessLoggerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return EntityAccessLogger
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EntityAccessLogger
    {
        return new EntityAccessLogger(
            $container->get(AuthorizationService::class),
            $container->get('CommandHandlerManager')
        );
    }
}

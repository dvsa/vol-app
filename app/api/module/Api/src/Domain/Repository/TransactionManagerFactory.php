<?php

/**
 * Class TransactionManager
 * @package Dvsa\Olcs\Api\Domain\Repository
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class TransactionManager
 * @package Dvsa\Olcs\Api\Domain\Repository
 */
final class TransactionManagerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TransactionManager
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransactionManager
    {
        return new TransactionManager($container->get('doctrine.entitymanager.orm_default'));
    }
}

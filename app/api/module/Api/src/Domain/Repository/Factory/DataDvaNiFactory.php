<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Factory;

use Dvsa\Olcs\Api\Domain\Repository\DataDvaNi;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for @see Dvsa\Olcs\Api\Domain\Repository\DataDvaNi
 *
 */
class DataDvaNiFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DataDvaNi
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DataDvaNi
    {
        return new DataDvaNi(
            $container->get('doctrine.connection.export')
        );
    }
}

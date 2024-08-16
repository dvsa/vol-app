<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Factory;

use Dvsa\Olcs\Api\Domain\Repository\DataGovUk;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for @see Dvsa\Olcs\Api\Domain\Repository\DataGovUk
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class DataGovUkFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DataGovUk
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DataGovUk
    {
        return new DataGovUk(
            $container->get('doctrine.connection.export')
        );
    }
}

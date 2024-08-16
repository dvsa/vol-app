<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Factory;

use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseVsOlcsDiffs;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for @see Dvsa\Olcs\Api\Domain\Repository\CompanyHouseVsOlcsDiffs
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class CompaniesHouseVsOlcsDiffsFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CompaniesHouseVsOlcsDiffs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CompaniesHouseVsOlcsDiffs
    {
        return new CompaniesHouseVsOlcsDiffs(
            $container->get('doctrine.connection.export')
        );
    }
}

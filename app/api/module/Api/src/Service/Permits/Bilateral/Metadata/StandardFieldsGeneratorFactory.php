<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class StandardFieldsGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return StandardFieldsGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): StandardFieldsGenerator
    {
        return new StandardFieldsGenerator(
            $container->get('PermitsBilateralMetadataCurrentFieldValuesGenerator')
        );
    }
}

<?php

namespace Olcs\Data\Mapper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class IrhpApplicationFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpApplication
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : IrhpApplication
    {
        return new IrhpApplication(
            $container->get('QaApplicationStepsPostDataTransformer')
        );
    }
}

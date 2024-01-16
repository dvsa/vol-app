<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\RefData;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SubmissionActionTypesFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return SubmissionActionTypes
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SubmissionActionTypes
    {
        return new SubmissionActionTypes(
            $container->get(AbstractDataServiceServices::class),
            $container->get(RefData::class)
        );
    }
}

<?php

namespace Permits\Data\Mapper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class CandidatePermitSelectionFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CandidatePermitSelection
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : CandidatePermitSelection
    {
        return $this->__invoke($serviceLocator, CandidatePermitSelection::Class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return CandidatePermitSelection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : CandidatePermitSelection
    {
        return new CandidatePermitSelection(
            $container->get('QaCommonHtmlAdder'),
            $container->get('Table')
        );
    }
}

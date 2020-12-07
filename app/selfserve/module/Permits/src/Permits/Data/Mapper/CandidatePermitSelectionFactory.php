<?php

namespace Permits\Data\Mapper;

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
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CandidatePermitSelection(
            $serviceLocator->get('QaCommonHtmlAdder'),
            $serviceLocator->get('Table')
        );
    }
}

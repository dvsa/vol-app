<?php

namespace Permits\Data\Mapper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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

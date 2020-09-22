<?php

namespace Permits\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class QaControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return QaController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $mainServiceLocator = $serviceLocator->getServiceLocator();

        return new QaController(
            $mainServiceLocator->get('QaFormProvider'),
            $mainServiceLocator->get('QaTemplateVarsGenerator'),
            $mainServiceLocator->get('Helper\Translation'),
            $mainServiceLocator->get('QaViewGeneratorProvider'),
            $mainServiceLocator->get('QaApplicationStepsPostDataTransformer')
        );
    }
}

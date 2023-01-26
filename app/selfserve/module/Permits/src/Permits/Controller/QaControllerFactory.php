<?php

namespace Permits\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class QaControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return QaController
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : QaController
    {
        return $this->__invoke($serviceLocator, QaController::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return QaController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : QaController
    {
        $parentLocator = $container->getServiceLocator();
        return new QaController(
            $parentLocator->get('QaFormProvider'),
            $parentLocator->get('QaTemplateVarsGenerator'),
            $parentLocator->get('Helper\Translation'),
            $parentLocator->get('QaViewGeneratorProvider'),
            $parentLocator->get('QaApplicationStepsPostDataTransformer')
        );
    }
}

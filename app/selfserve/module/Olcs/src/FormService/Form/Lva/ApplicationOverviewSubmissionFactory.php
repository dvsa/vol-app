<?php

namespace Olcs\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Laminas\ServiceManager\FactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ApplicationOverviewSubmissionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ApplicationOverviewSubmission
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationOverviewSubmission
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }

        // Creates an instance of the ConcreteClass with the $formHelper dependency from the container
        return new ApplicationOverviewSubmission($container->get('Helper\Translation'), $container->get(FormHelperService::class));
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ApplicationOverviewSubmission
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ApplicationOverviewSubmission
    {
        return $this->__invoke($serviceLocator, ApplicationOverviewSubmission::class);
    }
}

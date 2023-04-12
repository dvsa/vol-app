<?php

namespace Olcs\FormService\Form\Lva;

use Laminas\ServiceManager\Factory\FactoryInterface;
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
        // Retrieve the $formHelper dependency from the $container
        $translationHelper = $container->getServiceLocator()->get('Helper\Translation');

        // Create an instance of the ConcreteClass with the $formHelper dependency
        return new ApplicationOverviewSubmission($translationHelper);
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

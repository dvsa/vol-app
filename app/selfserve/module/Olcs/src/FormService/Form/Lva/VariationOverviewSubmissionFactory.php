<?php

namespace Olcs\FormService\Form\Lva;

use Laminas\ServiceManager\FactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class VariationOverviewSubmissionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return VariationOverviewSubmission
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VariationOverviewSubmission
    {
        // Retrieve the $formHelper dependency from the $container
        $translationHelper = $container->getServiceLocator()->get('Helper\Translation');

        // Create an instance of the ConcreteClass with the $formHelper dependency
        return new VariationOverviewSubmission($translationHelper);
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return VariationOverviewSubmission
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createService(ServiceLocatorInterface $serviceLocator): VariationOverviewSubmission
    {
        return $this->__invoke($serviceLocator, VariationOverviewSubmission::class);
    }
}

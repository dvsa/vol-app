<?php

namespace Olcs\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
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
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }

        // Create an instance of the ConcreteClass with the $formHelper dependency retrieved from the container
        return new VariationOverviewSubmission($container->get('Helper\Translation'), $container->get(FormHelperService::class));
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

<?php

namespace Olcs\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

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
        // Creates an instance of the ConcreteClass with the $formHelper dependency from the container
        return new ApplicationOverviewSubmission($container->get('Helper\Translation'), $container->get(FormHelperService::class));
    }
}

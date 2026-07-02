<?php

namespace Common\Service\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;

class FormHelperServiceFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FormHelperService
    {
        return new FormHelperService(
            $container->get('FormAnnotationBuilder'),
            $container->get('Config'),
            $container->get(AuthorizationService::class),
            $container->get('ViewRenderer'),
            $container->get('Data\Address'),
            $container->get('Helper\Address'),
            $container->get('Helper\Date'),
            $container->get('Helper\Translation')
        );
    }
}

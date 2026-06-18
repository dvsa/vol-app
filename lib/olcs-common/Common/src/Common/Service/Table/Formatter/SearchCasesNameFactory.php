<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;

class SearchCasesNameFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SearchCasesName
    {
        $authService = $container->get(AuthorizationService::class);
        $urlHelper = $container->get('Helper\Url');
        return new SearchCasesName($authService, $urlHelper);
    }
}

<?php

namespace Common\Service\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TranslationHelperServiceFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TranslationHelperService
    {
        return new TranslationHelperService(
            $container->get('translator')
        );
    }
}

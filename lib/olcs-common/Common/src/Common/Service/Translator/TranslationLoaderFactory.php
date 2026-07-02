<?php

namespace Common\Service\Translator;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for the translation loader service (front end nodes)
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TranslationLoaderFactory implements FactoryInterface
{
    /**
     * @param string             $requestedName
     * @param array|null         $options
     *
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TranslationLoader
    {
        return new TranslationLoader($container->get('QueryService'));
    }
}

<?php

namespace Dvsa\Olcs\Api\Service\Translator;

use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * TranslationLoaderFactory for API nodes
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TranslationLoaderFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TranslationLoader
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TranslationLoader
    {
        $repoServiceManager = $container->get('RepositoryServiceManager');

        return new TranslationLoader(
            $container->get(CacheEncryption::class),
            $repoServiceManager->get('TranslationKeyText'),
            $repoServiceManager->get('Replacement')
        );
    }
}

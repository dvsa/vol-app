<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Utils\Translation;

use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Psr\Container\ContainerInterface;

class TranslatorDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, callable $callback, array $options = null)
    {
        /** @var Translator $realTranslator */
        $realTranslator = $callback();

        $config = $container->get('Config');

        // Re-use the configured remote translation loader to source the replacement tokens.
        $loaderClass = $config['translator']['remote_translation'][0]['type'];
        $translationLoader = $realTranslator->getPluginManager()->get($loaderClass);
        $replacements = new Replacements($translationLoader->loadReplacements());

        return new TranslatorDelegator($realTranslator, $replacements);
    }
}

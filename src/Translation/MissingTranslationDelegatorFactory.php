<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Utils\Translation;

use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Attaches the MissingTranslationProcessor listener to the translator's own
 * event manager at service construction time. This replaces the per-app
 * `Module::onBootstrap()` wiring so the registration is contained inside
 * olcs-utils and no longer depends on the MVC module lifecycle.
 */
class MissingTranslationDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, callable $callback, array $options = null)
    {
        $translator = $callback();

        $underlying = $translator instanceof TranslatorDelegator ? $translator->getTranslator() : $translator;

        if (!$underlying instanceof Translator) {
            return $translator;
        }

        $underlying->enableEventManager();

        /** @var MissingTranslationProcessor $processor */
        $processor = $container->get(MissingTranslationProcessor::class);
        $processor->attach($underlying->getEventManager());

        return $translator;
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Utils\Translation;

use Closure;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\TemplatePathStack;
use Psr\Container\ContainerInterface;

/**
 * Builds the MissingTranslationProcessor with explicit collaborators rather
 * than reach-through service lookups inside the listener.
 */
class MissingTranslationProcessorFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null,
    ): MissingTranslationProcessor {
        /** @var RendererInterface $renderer */
        $renderer = $container->get('ViewRenderer');
        /** @var TemplatePathStack $resolver */
        $resolver = $container->get(TemplatePathStack::class);

        $placeholder = null;
        $viewHelperManager = $container->get('ViewHelperManager');
        if ($viewHelperManager->has('getPlaceholder')) {
            // `getPlaceholder` is registered via GetPlaceholderFactory, which
            // returns a Closure of signature
            // `fn(string $name): \Dvsa\Olcs\Utils\View\Helper\GetPlaceholder`.
            $placeholder = $viewHelperManager->get('getPlaceholder');
            assert($placeholder instanceof Closure);
        }

        return new MissingTranslationProcessor($renderer, $resolver, $placeholder);
    }
}

<?php

declare(strict_types=1);

namespace Common\Form\View\Helper;

use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\ServiceManager\Initializer\InitializerInterface;
use Override;
use Psr\Container\ContainerInterface;

/**
 * Registers aria- and x- as translatable attribute prefixes on all Laminas form
 * view helpers, so that aria-label="translation.key" and x-custom="translation.key"
 * values are passed through the translator when rendering HTML attributes.
 */
class TranslatableAttributePrefixInitializer implements InitializerInterface
{
    #[Override]
    public function __invoke(ContainerInterface $container, mixed $instance): void
    {
        if ($instance instanceof AbstractHelper) {
            $instance->addTranslatableAttributePrefix('aria-');
            $instance->addTranslatableAttributePrefix('x-');
        }
    }
}

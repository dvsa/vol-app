<?php

declare(strict_types=1);

namespace Common\Form\View\Helper;

use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @see FormElementErrors
 * @see \CommonTest\Form\View\Helper\FormErrorsFactoryTest
 */
class FormElementErrorsFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param null|array $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FormElementErrors
    {
        return new FormElementErrors(
            $container->get(FormElementMessageFormatter::class),
            // Uses 'translator' to ensure same instance as LanguageListener so that locale is correct for translation
            $container->get('translator')
        );
    }
}

<?php

declare(strict_types=1);

namespace Common\Form\View\Helper;

use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Psr\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @see FormErrors
 * @see \CommonTest\Form\View\Helper\FormErrorsFactoryTest
 */
class FormErrorsFactory implements FactoryInterface
{
    /**
     * @param mixed $requestedName
     * @param array|null $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FormErrors
    {
        return new FormErrors(
            $container->get(FormElementMessageFormatter::class),
            $container->get(TranslatorInterface::class)
        );
    }
}

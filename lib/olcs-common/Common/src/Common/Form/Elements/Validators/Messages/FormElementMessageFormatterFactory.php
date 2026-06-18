<?php

declare(strict_types=1);

namespace Common\Form\Elements\Validators\Messages;

use Psr\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @see FormElementMessageFormatter
 * @see \CommonTest\Form\Elements\Validators\Messages\FormElementMessageFormatterFactoryTest
 */
class FormElementMessageFormatterFactory implements FactoryInterface
{
    /**
     * @param mixed $requestedName
     * @param array|null $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FormElementMessageFormatter
    {
        $formatter = new FormElementMessageFormatter($container->get(TranslatorInterface::class));

        $messageTemplates = [];
        if ($container->has('config')) {
            $config = $container->get('config');
            $messageTemplates = $config['validation']['default_message_templates_to_replace'] ?? [];
        }

        $validatorPluginManager = $container->get('ValidatorManager');
        foreach ($messageTemplates as $messageKey => $validatorClassReference) {
            $formatter->enableReplacementOfMessage($messageKey, new ValidatorDefaultMessageProvider($validatorPluginManager, $validatorClassReference));
        }

        return $formatter;
    }
}

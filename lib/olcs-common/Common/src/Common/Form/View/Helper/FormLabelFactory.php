<?php

declare(strict_types=1);

namespace Common\Form\View\Helper;

use Laminas\Form\View\Helper\FormLabel;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Override;
use Psr\Container\ContainerInterface;

class FormLabelFactory implements FactoryInterface
{
    #[Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FormLabel
    {
        $instance = new FormLabel();
        $translator = $container->get(TranslatorInterface::class);
        assert($translator instanceof TranslatorInterface);
        $instance->setTranslator($translator);
        return $instance;
    }
}

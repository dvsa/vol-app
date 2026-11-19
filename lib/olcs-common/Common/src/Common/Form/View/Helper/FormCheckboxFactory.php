<?php

declare(strict_types=1);

namespace Common\Form\View\Helper;

use Laminas\Form\View\Helper\FormCheckbox as LaminasFormCheckbox;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class FormCheckboxFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FormCheckbox
    {
        return new FormCheckbox(new LaminasFormCheckbox());
    }
}

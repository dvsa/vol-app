<?php

declare(strict_types=1);

namespace Common\Form\View\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @see FormRow
 */
class FormRowFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FormRow
    {
        $mainConfig = $container->get('Config');
        $config = $mainConfig['form_row'] ?? [];

        return new FormRow($config);
    }
}

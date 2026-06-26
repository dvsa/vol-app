<?php

namespace Common\Service\Qa\Custom\Common;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DateBeforeValidatorFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DateBeforeValidator
    {
        $options ??= [];

        return new DateBeforeValidator(
            $container->get('ViewHelperManager')->get('DateFormat'),
            $container->get('QaDateTimeFactory'),
            $options
        );
    }
}

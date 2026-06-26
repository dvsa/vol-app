<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class InfoIconAdderFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InfoIconAdder
    {
        return new InfoIconAdder(
            $container->get('Helper\Translation'),
            $container->get('QaCommonHtmlAdder')
        );
    }
}

<?php

declare(strict_types=1);

namespace Common\View\Helper;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ImportantPrefixFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ImportantPrefix
    {
        return new ImportantPrefix($container->get('Helper\Translation'));
    }
}

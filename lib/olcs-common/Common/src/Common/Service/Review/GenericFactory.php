<?php

namespace Common\Service\Review;

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\FormatterPluginManager;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GenericFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new $requestedName(
            $container->get(AbstractReviewServiceServices::class),
            $container->get(FormatterPluginManager::class)->get(Address::class)
        );
    }
}

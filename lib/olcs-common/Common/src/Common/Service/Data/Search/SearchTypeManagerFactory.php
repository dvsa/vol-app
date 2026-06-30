<?php

namespace Common\Service\Data\Search;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SearchTypeManagerFactory implements FactoryInterface
{
    public const MISSING_CONFIG_MESSAGE = 'Search config is missing';

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SearchTypeManager
    {
        $config = $container->get('Config');

        if (!isset($config['search'])) {
            throw new \RuntimeException(self::MISSING_CONFIG_MESSAGE);
        }

        return new SearchTypeManager($container, $config['search']);
    }
}

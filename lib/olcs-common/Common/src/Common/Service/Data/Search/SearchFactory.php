<?php

namespace Common\Service\Data\Search;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SearchFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Search
    {
        return new Search(
            $container->get('Table'),
            $container->get('ViewHelperManager'),
            $container->get(SearchTypeManager::class)
        );
    }
}

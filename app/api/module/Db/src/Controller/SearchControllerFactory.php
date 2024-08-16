<?php

namespace Dvsa\Olcs\Db\Controller;

use Dvsa\Olcs\Api\Service\Permits\CandidatePermits\IrhpCandidatePermitsCreator;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Search Controller Factory
 */
class SearchControllerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpCandidatePermitsCreator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SearchController
    {
        $elasticSearchService = $container->get('Elasticsearch\Search');

        return new SearchController(
            $elasticSearchService
        );
    }
}

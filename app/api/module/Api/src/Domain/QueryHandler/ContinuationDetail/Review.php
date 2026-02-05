<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Generator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Continuation detail review
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Review extends AbstractQueryHandler
{
    protected $repoServiceName = 'ContinuationDetail';

    /**
     * @var Generator
     */
    protected $continuationReviewService;

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        return [
            'markup' => $this->continuationReviewService
                ->generate(
                    $this->getRepo()->fetchUsingId($query)
                )
        ];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Review
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->continuationReviewService = $container->get('ContinuationReview');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}

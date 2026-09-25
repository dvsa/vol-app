<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationUndertakingsReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationUndertakingsReviewService;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Declaration Undertakings
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeclarationUndertakings extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    /**
     * @var ApplicationUndertakingsReviewService
     */
    private ApplicationUndertakingsReviewService $applicationReviewService;

    private VariationUndertakingsReviewService $variationReviewService;

    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $application,
            [
                'licence' => [
                    'organisation' => [
                        'type'
                    ]
                ]
            ],
            [
                'undertakings' => $this->getUndertakings($application),
            ]
        );
    }

    protected function getUndertakings(ApplicationEntity $application)
    {
        $data = $application->serialize();
        $data['isGoods'] = $application->isGoods();
        $data['isInternal'] = false;

        $reviewService = $application->isVariation()
            ? $this->variationReviewService
            : $this->applicationReviewService;

        return $reviewService->getLongTextMarkup($data);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DeclarationUndertakings
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $fullContainer = $container;

        $this->applicationReviewService = $container->get('Review\ApplicationUndertakings');
        $this->variationReviewService = $container->get('Review\VariationUndertakings');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}

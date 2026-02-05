<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Service\Permits\Availability\CandidatePermitsAvailableCountCalculator;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * IrhpPermitRanges by IrhpApplication
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class RangesByIrhpApplication extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpApplication';
    protected $bundle = ['countrys', 'emissionsCategory'];

    /** @var CandidatePermitsAvailableCountCalculator $candidatePermitsAvailableCountCalculator */
    protected $candidatePermitsAvailableCountCalculator;

    /**
     * @param QueryInterface $query query
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var IrhpApplication $irhpApplication */
        $irhpApplication = $this->getRepo()->fetchById($query->getIrhpApplication());

        $ranges = $irhpApplication
            ->getAssociatedStock()
            ->getNonReservedNonReplacementRangesOrderedByFromNo();

        foreach ($ranges as &$range) {
            $range->remainingPermits =
                $this->candidatePermitsAvailableCountCalculator->getCount($range, 0);
        }

        return [
            'ranges' => $this->resultList($ranges, $this->bundle),
            'count' => count($ranges)
        ];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return RangesByIrhpApplication
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->candidatePermitsAvailableCountCalculator = $container->get('PermitsAvailabilityCandidatePermitsAvailableCountCalculator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}

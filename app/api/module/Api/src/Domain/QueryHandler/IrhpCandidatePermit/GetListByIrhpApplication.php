<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpCandidatePermit;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Service\Permits\Common\RangeBasedRestrictedCountriesProvider;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Get IRHP Candidate Permits by IRHP Application id
 */
class GetListByIrhpApplication extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpCandidatePermit';
    protected $bundle = [
        'irhpPermitRange' => [
            'countrys' => [
                'country'
            ],
            'emissionsCategory',
        ],
        'irhpPermitApplication'
    ];

    /** @var RangeBasedRestrictedCountriesProvider */
    private $restrictedCountriesProvider;

    /**
     * Handle list query
     *
     * @param QueryInterface $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        // fetch list of candidate permits
        $irhpCandidatePermits = $this->resultList(
            $repo->fetchList($query, Query::HYDRATE_OBJECT),
            $this->bundle
        );

        foreach ($irhpCandidatePermits as $i => $irhpCandidatePermit) {
            // set value of permit number
            $permitNumber = $i + 1;
            if ($query instanceof PagedQueryInterface) {
                $permitNumber += (($query->getPage() - 1) * $query->getLimit());
            }
            $irhpCandidatePermit['permitNumber'] = $permitNumber;

            // set value of constrained countries
            $irhpCandidatePermit['constrainedCountries']
                = $this->restrictedCountriesProvider->getList($irhpCandidatePermit['irhpPermitRange']['id']);

            $irhpCandidatePermits[$i] = $irhpCandidatePermit;
        }

        return [
            'results' => $irhpCandidatePermits,
            'count' => $repo->fetchCount($query)
        ];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return GetListByIrhpApplication
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->restrictedCountriesProvider
            = $container->get('PermitsCommonRangeBasedRestrictedCountriesProvider');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}

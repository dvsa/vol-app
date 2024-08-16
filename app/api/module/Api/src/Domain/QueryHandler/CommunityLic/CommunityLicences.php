<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepository;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Transfer\Query\CommunityLic\CommunityLicences as CommunityLicencesQuery;

/**
 * Community Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @see CommunityLicencesQuery
 */
class CommunityLicences extends AbstractQueryHandler
{
    protected $repoServiceName = 'CommunityLic';

    protected $extraRepos = ['Licence'];

    /**
     * Handle query
     *
     * @param CommunityLicencesQuery|QueryInterface $query
     * @return array
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        assert($query instanceof CommunityLicencesQuery, 'Expected instance of CommunityLicencesQuery');

        $communityLicenceRepository = $this->getRepo();
        assert($communityLicenceRepository instanceof CommunityLicRepo, 'Expected instance of CommunityLicRepo');

        $licenceRepository = $this->getRepo('Licence');
        assert($licenceRepository instanceof LicenceRepository, 'Expected instance of LicenceRepository');

        $licence = $licenceRepository->fetchById($query->getLicence());
        assert($licence instanceof Licence, 'Expected instance of Licence');

        $officeCopy = $communityLicenceRepository->fetchOfficeCopy($query->getLicence());

        $data = $query->getArrayCopy();

        unset($data['statuses']);

        $unfilteredQuery = \Dvsa\Olcs\Transfer\Query\CommunityLic\CommunityLicences::create($data);

        return [
            'result' => $this->resultList(
                $communityLicenceRepository->fetchList($query, Query::HYDRATE_OBJECT)
            ),
            'count' =>  $communityLicenceRepository->fetchCount($query),
            'count-unfiltered' => $communityLicenceRepository->hasRows($unfilteredQuery),
            'totCommunityLicences' => $licence->getTotCommunityLicences(),
            'totActiveCommunityLicences' => $communityLicenceRepository->countActiveByLicenceId($licence->getId()),
            'officeCopy' => $this->result($officeCopy)->serialize()
        ];
    }
}

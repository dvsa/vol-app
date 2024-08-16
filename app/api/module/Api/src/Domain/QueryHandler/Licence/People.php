<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * People
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class People extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';
    protected $extraRepos = ['OrganisationPerson'];

    /**
     * Handle Query
     *
     * @param \Dvsa\Olcs\Transfer\Query\Licence\People $query Query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        /* @var $licence \Dvsa\Olcs\Api\Entity\Licence\Licence */
        $licence = $this->getRepo()->fetchUsingId($query);

        /** @var Repository\OrganisationPerson $orgPersonRepo */
        $orgPersonRepo = $this->getRepo('OrganisationPerson');

        $organisationPersons = $orgPersonRepo->fetchListForOrganisation(
            $licence->getOrganisation()->getId()
        );

        $org = $licence->getOrganisation();

        return $this->result(
            $licence,
            [
                'organisation' => ['type']
            ],
            [
                'hasInforceLicences' => $org->hasInforceLicences(),
                'isExceptionalType' => $org->isPartnership() || $org->isSoleTrader(),
                'isSoleTrader' => $org->isSoleTrader(),
                'people' => $this->resultList($organisationPersons, ['person' => ['title']]),
            ]
        );
    }
}

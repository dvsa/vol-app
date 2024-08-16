<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Psv Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvDiscs extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['PsvDisc'];

    public function handleQuery(QueryInterface $query)
    {
        /** @var LicenceEntity $licence */
        $licence = $this->getRepo()->fetchUsingId($query);

        $psvDiscRepo = $this->getRepo('PsvDisc');

        return $this->result(
            $licence,
            [],
            [
                'psvDiscs' => $psvDiscRepo->fetchList($query),
                'remainingSpacesPsv' => $licence->getRemainingSpacesPsv(),
                'totalPsvDiscs' => $psvDiscRepo->fetchCount($query),
            ]
        );
    }
}

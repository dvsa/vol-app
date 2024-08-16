<?php

/**
 * Other Active Licences
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Other Active Licences
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OtherActiveLicences extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    public function handleQuery(QueryInterface $query)
    {
        /** @var LicenceEntity $licence */
        $licence = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $licence,
            [],
            [
                'otherActiveLicences' => $this->resultList($licence->getOtherActiveLicences())
            ]
        );
    }
}

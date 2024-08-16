<?php

/**
 * ConditionUndertaking
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * ConditionUndertaking
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ConditionUndertaking extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';
    protected $extraRepos = ['ConditionUndertaking'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var $licence LicenceEntity */
        $licence = $this->getRepo()->fetchUsingId($query);

        $conditionUndertakings = $this->getRepo('ConditionUndertaking')->fetchListForLicenceReadOnly($query->getId());

        return $this->result(
            $licence,
            [],
            [
                'conditionUndertakings' => $this->resultList(
                    $conditionUndertakings,
                    ['attachedTo', 'conditionType', 'operatingCentre' => ['address']]
                )
            ]
        );
    }
}

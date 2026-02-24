<?php

/**
 * Cease Discs For Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository\Query\Discs;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc;

/**
 * Cease Discs For Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CeaseDiscsForLicence extends AbstractRawQuery
{
    protected $templateMap = [
        'pd' => PsvDisc::class
    ];

    protected $queryTemplate = 'UPDATE {pd}
      SET {pd.ceasedDate} = :ceasedDate, {pd.lastModifiedOn} = NOW(), {pd.lastModifiedBy} = :currentUserId
      WHERE {pd.licence} = :licence AND {pd.ceasedDate} IS NULL';

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getParams()
    {
        $today = new DateTime();

        return [
            'ceasedDate' => $today->format('Y-m-d H:i:s')
        ];
    }
}

<?php

/**
 * Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OtherLicence extends AbstractQueryHandler
{
    protected $repoServiceName = 'OtherLicence';

    public function handleQuery(QueryInterface $query)
    {
        $otherLicence = $this->getRepo()->fetchUsingId($query);
        return $this->result(
            $otherLicence,
            [
                'transportManagerApplication',
                'transportManagerLicence'
            ]
        );
    }
}

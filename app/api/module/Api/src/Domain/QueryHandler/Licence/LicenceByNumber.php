<?php

/**
 * LicenceByNumber.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Class LicenceByNumber
 *
 * @package Dvsa\Olcs\Api\Domain\QueryHandler\Licence
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class LicenceByNumber extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    public function handleQuery(QueryInterface $query)
    {
        /** @var Licence $licence */
        $licence = $this->getRepo()->fetchByLicNo($query->getLicenceNumber());

        return $this->result(
            $licence,
            [
                'operatingCentres' => [
                    'operatingCentre' => [
                        'address',
                        'conditionUndertakings' => [
                            'conditionType',
                            'licence'
                        ]
                    ]
                ]
            ]
        );
    }
}

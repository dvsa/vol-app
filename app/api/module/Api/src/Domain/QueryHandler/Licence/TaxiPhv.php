<?php

/**
 * TaxiPhv
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * TaxiPhv
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TaxiPhv extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';
    protected $extraRepos = ['TrafficArea'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var $licence LicenceEntity */
        $licence = $this->getRepo()->fetchWithPrivateHireLicence($query->getId());

        return $this->result(
            $licence,
            [
                'trafficArea',
                'privateHireLicences' => [
                    'contactDetails' => [
                        'address' => [
                            'countryCode'
                        ]
                    ]
                ]
            ],
            [
                'trafficAreaOptions' => $this->getRepo('TrafficArea')->getValueOptions()
            ]
        );
    }
}

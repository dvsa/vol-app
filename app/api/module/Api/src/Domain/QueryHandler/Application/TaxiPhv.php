<?php

/**
 * TaxiPhv
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * TaxiPhv
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TaxiPhv extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';
    protected $extraRepos = ['TrafficArea', 'Licence'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var $application Dvsa\Olcs\Api\Entity\Application\Application */
        $application = $this->getRepo()->fetchUsingId($query);
        $this->getRepo('Licence')->fetchWithPrivateHireLicence($application->getLicence()->getId());

        return $this->result(
            $application,
            [
                'licence' => [
                    'trafficArea',
                    'privateHireLicences' => [
                        'contactDetails' => [
                            'address' => [
                                'countryCode'
                            ]
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

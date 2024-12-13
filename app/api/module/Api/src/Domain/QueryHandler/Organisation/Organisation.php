<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Organisation;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class Organisation extends AbstractQueryHandler
{
    protected $repoServiceName = 'Organisation';

    protected $extraRepos = ['TrafficArea'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var $organisation \Dvsa\Olcs\Api\Entity\Organisation\Organisation */
        $organisation = $this->getRepo()->fetchUsingId($query);
        $allowedOperatorLocation = $organisation->getAllowedOperatorLocation();

        return $this->result(
            $organisation,
            [
                'disqualifications',
            ],
            [
                'isDisqualified' => $organisation->getDisqualifications()->count() > 0,
                'taValueOptions' => $this->getTrafficAreaValueOptions($allowedOperatorLocation),
                'allowedOperatorLocation' => $allowedOperatorLocation,
                'hasOperatorAdmin' => $organisation->hasOperatorAdmin()
            ]
        );
    }

    /**
     * Get traffic area valueOptions
     *
     * @param string $allowedOperatorLocation
     * @return array
     */
    protected function getTrafficAreaValueOptions($allowedOperatorLocation)
    {
        $taList = $this->getRepo('TrafficArea')->fetchListForNewApplication($allowedOperatorLocation);
        $valueOptions = [];
        foreach ($taList as $ta) {
            $valueOptions[$ta->getId()] = $ta->getName();
        }
        return $valueOptions;
    }
}

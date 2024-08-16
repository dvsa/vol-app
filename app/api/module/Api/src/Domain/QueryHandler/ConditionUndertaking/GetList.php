<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Get a list of Users
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'ConditionUndertaking';
    protected $extraRepos = ['Application'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var $query \Dvsa\Olcs\Transfer\Query\ConditionUndertaking\GetList */

        if (empty($query->getApplication()) && empty($query->getLicence())) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(
                ['You must specifiy either application or licence']
            );
        }
        if (!empty($query->getApplication()) && !empty($query->getLicence())) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(
                ['You must specifiy either (not both) application or licence']
            );
        }

        if (!empty($query->getApplication())) {
            /* @var $application \Dvsa\Olcs\Api\Entity\Application\Application */
            $application = $this->getRepo('Application')->fetchById($query->getApplication());
            if ($application->getIsVariation()) {
                $results = $this->getVariationList($application);
            } else {
                $results = $this->getApplicationList($application);
            }
        } else {
            $results = $this->getLicenceList(
                $query->getLicence(),
                $query->getConditionType()
            );
        }

        return [
            'result' => $this->resultList(
                $results,
                [
                    'operatingCentre' => ['address'],
                    'licConditionVariation',
                    's4',
                ]
            ),
            'count' => count($results)
        ];
    }

    /**
     * Get a list of ConditionUndertaking for an Application
     *
     *
     * @return array
     */
    protected function getApplicationList(\Dvsa\Olcs\Api\Entity\Application\Application $application)
    {
        return $this->getRepo()->fetchListForApplication($application->getId());
    }

    /**
     * Get a list of ConditionUndertaking for an Licence
     *
     * @param int $licenceId
     * @param string $conditionType
     *
     * @return array
     */
    protected function getLicenceList($licenceId, $conditionType = null)
    {
        return $this->getRepo()->fetchListForLicence($licenceId, $conditionType);
    }

    /**
     * Get a list of ConditionUndertaking for an Variation
     *
     *
     * @return array
     */
    protected function getVariationList(\Dvsa\Olcs\Api\Entity\Application\Application $application)
    {
        return $this->getRepo()->fetchListForVariation(
            $application->getId(),
            $application->getLicence()->getId()
        );
    }
}

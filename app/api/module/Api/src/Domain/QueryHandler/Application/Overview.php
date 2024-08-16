<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Application\ApplicationTracking as ApplicationTrackingEntity;
use Dvsa\Olcs\Transfer\Query\Licence\Overview as LicenceOverviewQry;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;

/**
 * Application Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Overview extends AbstractQueryHandler
{
    public const OPERATING_CENTRES_SECTION = 'operatingCentres';

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['Fee', 'Opposition'];

    public function handleQuery(QueryInterface $query)
    {
        /* @var $application \Dvsa\Olcs\Api\Entity\Application\Application */
        $application = $this->getRepo()->fetchUsingId($query);

        if ($query->getValidateAppCompletion() && $application->isVariation()) {
            $this->getCommandHandler()->handleCommand(
                UpdateApplicationCompletionCmd::create(
                    ['id' => $application->getId(), 'section' => self::OPERATING_CENTRES_SECTION]
                )
            );
        }

        $licenceQuery = LicenceOverviewQry::create(['id' => $application->getLicence()->getId()]);
        $licence = $this->getQueryHandler()->handleQuery($licenceQuery, false);

        return $this->result(
            $application,
            [
                'applicationCompletion',
                'applicationTracking',
                'status',
                'interimStatus',
                'licenceType',
                'goodsOrPsv',
            ],
            [
                'licence' => $licence->serialize(),
                'feeCount' => $this->getFeeCount($application),
                'oppositionCount' => $this->getOppositionCount($application),
                'valueOptions' => [
                    'tracking' => ApplicationTrackingEntity::getValueOptions(),
                ],
                'outOfOppositionDate' => $this->getDateOrString($application->getOutOfOppositionDate()),
                'outOfRepresentationDate' => $this->getDateOrString($application->getOutOfRepresentationDate()),
                'operatingCentresNetDelta' => $application->getOperatingCentresNetDelta(),
                'licenceVehicles' => $this->resultList($application->getActiveVehicles()),
                'applicableAuthProperties' => $application->getApplicableAuthProperties(),
            ]
        );
    }

    protected function getFeeCount($application)
    {
        $fees = $this->getRepo('Fee')->fetchOutstandingFeesByApplicationId($application->getId());
        return count($fees);
    }

    protected function getOppositionCount($application)
    {
        $oppositions = $this->getRepo('Opposition')->fetchByApplicationId($application->getId());
        return count($oppositions);
    }

    /**
     * Conveert param into a string (ie if its a date)
     *
     * @param \DateTime|string $value
     *
     * @return string If param was a DateTime then returns a string version of the date, otherwise return param
     */
    protected function getDateOrString($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        return $value;
    }
}

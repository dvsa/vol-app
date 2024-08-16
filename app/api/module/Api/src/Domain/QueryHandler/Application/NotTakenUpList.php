<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddDays;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddDaysExcludingDates;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddWorkingDays;
use Dvsa\Olcs\Api\Domain\Util\DateTime\PublicHolidayDateProvider;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Gets a list of applications ready to go Not Taken Up
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class NotTakenUpList extends AbstractQueryHandler
{
    public const NTU_WORKING_DAYS = 15;

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['TrafficArea', 'PublicHoliday'];

    public function handleQuery(QueryInterface $query)
    {
        $results = $this->getApplicationsForNtu($query->getDate());

        return [
            'result' => $this->resultList($results),
            'count' => count($results)
        ];
    }

    protected function getApplicationsForNtu($now)
    {
        $trafficAreas = $this->getRepo('TrafficArea')->fetchAll();
        $applications = $this->getRepo()->fetchForNtu();

        $dateTimeProcessors = [];

        $dateTimeDaysProcessor = new AddDays();
        $dateTimeWorkingDaysProcessor = new AddWorkingDays($dateTimeDaysProcessor);

        // need to process separately for each TA because we can have different public holidays for different areas
        foreach ($trafficAreas as $trafficArea) {
            $dateProvider = new PublicHolidayDateProvider($this->getRepo('PublicHoliday'), $trafficArea);
            $dateTimeProcessors[$trafficArea->getId()] =
                new AddDaysExcludingDates($dateTimeWorkingDaysProcessor, $dateProvider);
        }
        $applicationsForNtu = [];

        foreach ($applications as $application) {
            $dateTimeProcessor = $dateTimeProcessors[$application->getLicence()->getTrafficArea()->getId()];
            $ntuDate = $dateTimeProcessor->calculateDate(
                new DateTime($application->getGrantedDate()),
                self::NTU_WORKING_DAYS
            );

            if ($now > $ntuDate) {
                $applicationsForNtu[] = $application;
            }
        }
        return $applicationsForNtu;
    }
}

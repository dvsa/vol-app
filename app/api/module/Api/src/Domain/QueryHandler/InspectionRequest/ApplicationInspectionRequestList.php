<?php

/**
 * Application Inspection Request List
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\InspectionRequestRepo;

/**
 * Application Inspection Request List
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationInspectionRequestList extends AbstractQueryHandler
{
    protected $repoServiceName = 'InspectionRequest';

    protected $extraRepos = ['Application'];

    public function handleQuery(QueryInterface $query)
    {
        $application = $this->getRepo('Application')->fetchWithLicence($query->getApplication());
        $licenceId = $application->getLicence()->getId();
        $inspectionRequest = $this->getRepo()->fetchPage($query, $licenceId);
        return [
            'result' => $this->resultList(
                $inspectionRequest['result'],
                [
                    'reportType',
                    'requestType',
                    'resultType',
                    'licence',
                    'application'
                ]
            ),
            'count' => $inspectionRequest['count']
        ];
    }
}

<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail;
use Dvsa\Olcs\Transfer\Query;

/**
 * Checklist reminders
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ChecklistReminders extends AbstractQueryHandler
{
    protected $repoServiceName = 'ContinuationDetail';

    /**
     * @param Query\ContinuationDetail\ChecklistReminders $query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(Query\QueryInterface $query)
    {
        /** @var ContinuationDetail $repo */
        $repo = $this->getRepo();

        $reminders = $repo->fetchChecklistReminders(
            $this->getInternalUserTrafficAreas(),
            $query->getMonth(),
            $query->getYear(),
            $query->getIds()
        );

        return [
            'result' => $this->resultList(
                $reminders,
                [
                    'licence' => [
                        'status',
                        'goodOrPsv',
                        'organisation',
                    ]
                ]
            ),
            'count' => count($reminders),
        ];
    }
}

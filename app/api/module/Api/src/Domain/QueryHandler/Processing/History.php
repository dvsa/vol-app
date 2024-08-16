<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Processing;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\EventHistory;
use Doctrine\ORM\Query;

/**
 * Event History query handler
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class History extends AbstractQueryHandler
{
    protected $repoServiceName = 'EventHistory';

    protected $extraRepos = ['Application', 'Licence', 'Cases'];

    /**
     * Handle the query
     *
     * @param QueryInterface $query Query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Transfer\Query\Processing\History $query */
        $this->modifyQuery($query);

        /** @var EventHistory $repo */
        $repo = $this->getRepo();
        $repo->disableSoftDeleteable();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                [
                    'licence',
                    'application',
                    'irhpApplication',
                    'organisation',
                    'transportManager',
                    'eventHistoryType',
                    'case',
                    'user' => [
                        'contactDetails' => [
                            'person'
                        ],
                        'team',
                    ]
                ]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }

    /**
     * Modify the query, by adding in the licence and organisation in specific scenarios
     *
     * @param \Dvsa\Olcs\Transfer\Query\Processing\History $query DTO query to modify
     *
     * @return void
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function modifyQuery(\Dvsa\Olcs\Transfer\Query\Processing\History $query)
    {
        $licence = null;
        $organisation = null;
        $transportManager = null;
        if ($query->getApplication()) {
            /** @var \Dvsa\Olcs\Api\Entity\Application\Application $application */
            $application = $this->getRepo('Application')->fetchById($query->getApplication());
            $licence = $application->getLicence();
            $organisation = $licence->getOrganisation();
        } elseif ($query->getLicence()) {
            /** @var \Dvsa\Olcs\Api\Entity\Licence\Licence $licence */
            $licence = $this->getRepo('Licence')->fetchById($query->getLicence());
            $organisation = $licence->getOrganisation();
        } elseif ($query->getCase()) {
            /** @var \Dvsa\Olcs\Api\Entity\Cases\Cases $case */
            $case = $this->getRepo('Cases')->fetchById($query->getCase());
            if ($case->getLicence()) {
                $licence = $case->getLicence();
                $organisation = $licence->getOrganisation();
            } elseif ($case->getTransportManager()) {
                $transportManager = $case->getTransportManager();
            }
        }

        if ($licence !== null) {
            $query->exchangeArray(['licence' => $licence->getId()]);
        }
        if ($organisation !== null) {
            $query->exchangeArray(['organisation' => $organisation->getId()]);
        }
        if ($transportManager !== null) {
            $query->exchangeArray(['transportManager' => $transportManager->getId()]);
        }
    }
}

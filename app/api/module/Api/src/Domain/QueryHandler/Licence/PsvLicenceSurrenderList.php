<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Task as TaskRepo;

/**
 * Gets a list of PSV licences to surrender
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PsvLicenceSurrenderList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['Task'];

    /**
     * Handle query
     *
     * @param \Dvsa\Olcs\Api\Domain\Query\Licence\PsvLicenceSurrenderList $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var LicenceRepo $licenceRepo */
        $licenceRepo = $this->getRepo();

        /** @var TaskRepo $taskRepo */
        $taskRepo = $this->getRepo('Task');

        $licenceIdsToSurrender = $licenceRepo->fetchPsvLicenceIdsToSurrender($query->getDate());
        $existedTasks = $taskRepo->fetchOpenedTasksForLicences(
            $licenceIdsToSurrender,
            Category::CATEGORY_LICENSING,
            SubCategory::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
            Task::TASK_DESCRIPTION_LICENCE_EXPIRED
        );

        $licenceIdsOfExistedTasks = [];
        array_walk(
            $existedTasks,
            function ($item) use (&$licenceIdsOfExistedTasks) {
                $licenceIdsOfExistedTasks[] = $item['licence']['id'];
            }
        );

        $results = array_diff($licenceIdsToSurrender, $licenceIdsOfExistedTasks);

        return [
            'result' => $results,
            'count' => count($results),
        ];
    }
}

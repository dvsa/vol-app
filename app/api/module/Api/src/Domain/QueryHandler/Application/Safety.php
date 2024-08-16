<?php

/**
 * Safety
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;

/**
 * Safety
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Safety extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['Workshop'];

    /**
     * Handle query
     *
     * @param QueryInterface $query DTO
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($query);

        $safetyDocuments = $application->getApplicationDocuments(
            $this->getRepo()->getCategoryReference(Category::CATEGORY_APPLICATION),
            $this->getRepo()->getSubCategoryReference(SubCategory::DOC_SUB_CATEGORY_MAINT_OTHER_DIGITAL)
        );

        $totalTrailers = $application->getTotAuthTrailers();
        return $this->result(
            $application,
            [
                'licence' => [
                    'tachographIns'
                ]
            ],
            [
                'safetyDocuments' => $this->resultList($safetyDocuments),
                'canHaveTrailers' => $application->canHaveTrailer(),
                'isShowTrailers' => ($totalTrailers > 0 || $totalTrailers === null),
                'workshops' => [
                    'results' => $this->resultList(
                        $this->getRepo('Workshop')->fetchList($query, Query::HYDRATE_OBJECT),
                        ['contactDetails' => ['address']]
                    ),
                    'count' => $this->getRepo('Workshop')->fetchCount($query),
                ],
            ]
        );
    }
}

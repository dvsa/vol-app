<?php

/**
 * Financial History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;

/**
 * Financial History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FinancialHistory extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    public function handleQuery(QueryInterface $query)
    {
        $applicationRepo = $this->getRepo();
        /* @var \Dvsa\Olcs\Api\Entity\Application\Application $application */
        $application = $applicationRepo->fetchUsingId($query, Query::HYDRATE_OBJECT);
        $financialDocuments = $application->getApplicationDocuments(
            $applicationRepo->getCategoryReference(Category::CATEGORY_LICENSING),
            $applicationRepo->getSubCategoryReference(SubCategory::DOC_SUB_CATEGORY_LICENCE_INSOLVENCY_DOCUMENT_DIGITAL)
        );

        return $this->result(
            $application,
            [],
            ['documents' => $this->resultList($financialDocuments)]
        );
    }
}

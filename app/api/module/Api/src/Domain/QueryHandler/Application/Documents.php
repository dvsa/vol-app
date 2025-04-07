<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\Application\Documents as DocumentsQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class Documents extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    /**
     * Handle query
     *
     * @param QueryInterface|DocumentsQry $query query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $irhpApplication = $this->getRepo()->fetchUsingId($query);

        return $this->resultList(
            $irhpApplication->getDocumentsByCategoryAndSubCategory(
                $query->getCategory(),
                $query->getSubCategory()
            )
        );
    }
}

<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\Documents as DocumentsQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Documents
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class Documents extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpApplication';

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

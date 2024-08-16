<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Document List
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DocumentList extends AbstractQueryHandler
{
    protected $repoServiceName = 'DocumentSearchView';

    /**
     * Handle query
     *
     * @param \Dvsa\Olcs\Transfer\Query\Document\DocumentList $query Query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        $data = $query->getArrayCopy();

        unset($data['category']);
        unset($data['documentSubCategory']);
        unset($data['isExternal']);
        unset($data['showDocs']);
        unset($data['format']);

        $unfilteredQuery = \Dvsa\Olcs\Transfer\Query\Document\DocumentList::create($data);

        /** @var  \Dvsa\Olcs\Api\Domain\Repository\DocumentSearchView $repo */
        $repo = $this->getRepo();

        $extensionList = [];
        if ($this->hasExtensionFilter($query)) {
            // only calculate extension list if it is used
            $extensionList = $repo->fetchDistinctListExtensions($unfilteredQuery);
        }

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT)
            ),
            'count' => $repo->fetchCount($query),
            'count-unfiltered' => $repo->hasRows($unfilteredQuery),
            'extensionList' => $extensionList,
        ];
    }

    /**
     * Does this document list use the extension filter
     *
     * @param \Dvsa\Olcs\Transfer\Query\Document\DocumentList $query DTO
     *
     * @return bool
     */
    private function hasExtensionFilter(\Dvsa\Olcs\Transfer\Query\Document\DocumentList $query)
    {
        // Only use the extension filter is document list is filter by something else, otherwise it will
        // attempt to get a list for the entire table
        return $query->getApplication() !== null
            || $query->getLicence() !== null
            || $query->getCase() !== null
            || $query->getBusReg() !== null
            || $query->getTransportManager() !== null
            || $query->getIrfoOrganisation() !== null
            || $query->getIrhpApplication() !== null;
    }
}

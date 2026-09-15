<?php

namespace Common\Service\Data\Search;

use Common\Service\Data\Search\Search as SearchService;

trait SearchAwareTrait
{
    /**
     * @var SearchService
     */
    protected $searchService;

    /**
     * @return SearchService
     */
    public function getSearchService()
    {
        return $this->searchService;
    }

    public function setSearchService(SearchService $searchService)
    {
        $this->searchService = $searchService;
        return $this;
    }
}

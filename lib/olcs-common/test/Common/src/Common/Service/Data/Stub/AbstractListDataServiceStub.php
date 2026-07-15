<?php

namespace CommonTest\Common\Service\Data\Stub;

use Common\Service\Data\AbstractListDataService;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class AbstractListDataServiceStub extends AbstractListDataService
{
    public $mockFetchListData;

    /**
     * function mock
     *
     * @param null $context Context
     *
     * @return mixed
     */
    #[\Override]
    public function fetchListData($context = null)
    {
        return $this->mockFetchListData;
    }
}

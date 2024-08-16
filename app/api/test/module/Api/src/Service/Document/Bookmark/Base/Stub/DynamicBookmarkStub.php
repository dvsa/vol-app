<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base\Stub;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

class DynamicBookmarkStub extends DynamicBookmark
{
    protected $queryResult = 'foo';

    protected $params = ['bar'];

    public function validateDataAndGetQuery($data)
    {
        return parent::validateDataAndGetQuery($data);
    }

    public function getQuery(array $data)
    {
        return $this->queryResult;
    }

    public function render()
    {
    }
}

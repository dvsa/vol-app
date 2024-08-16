<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\IrfoPsvAuthBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * BkmOperatorName
 */
class BkmOperatorName extends DynamicBookmark
{
    /**
     * Gets query to retrieve data
     *
     * @param array $data
     * @return Qry|null
     */
    public function getQuery(array $data)
    {
        return Qry::create(
            [
                'id' => $data['irfoPsvAuth'],
                'bundle' => [
                    'organisation'
                ]
            ]
        );
    }

    /**
     * Renders the bookmark
     *
     * @return string
     */
    public function render()
    {
        return !empty($this->data['organisation']['name']) ? $this->data['organisation']['name'] : '';
    }
}

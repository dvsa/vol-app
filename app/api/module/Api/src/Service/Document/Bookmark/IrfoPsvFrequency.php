<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\IrfoPsvAuthBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * IrfoPsvFrequency
 */
class IrfoPsvFrequency extends DynamicBookmark
{
    /**
     * Gets query to retrieve data
     *
     * @param array $data
     * @return Qry
     */
    public function getQuery(array $data)
    {
        return Qry::create(
            [
                'id' => $data['irfoPsvAuth']
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
        return (!empty($this->data['journeyFrequency']['description']))
            ? $this->data['journeyFrequency']['description']
            : '';
    }
}

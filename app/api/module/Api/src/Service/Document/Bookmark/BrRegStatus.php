<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;

/**
 * Class BrRegStatus
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrRegStatus extends DynamicBookmark
{
    /**
     * Get query
     *
     * @param array $data array of data
     *
     * @return Qry
     */
    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['busRegId'], 'bundle' => ['status']]);
    }

    /**
     * Render bookmark
     *
     * @return string
     */
    public function render()
    {
        if (!isset($this->data['status']['description'])) {
            return '';
        }

        return $this->data['status']['description'];
    }
}

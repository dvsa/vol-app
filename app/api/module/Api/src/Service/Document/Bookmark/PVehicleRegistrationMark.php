<?php

/**
 * PVehicleRegistrationMark
 */

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\ImpoundingBundle as Qry;

/**
 * PVehicleRegistrationMark
 */
class PVehicleRegistrationMark extends DynamicBookmark
{
    /**
     * Gets query to retrieve data
     *
     * @param array $data
     * @return Qry|null
     */
    public function getQuery(array $data)
    {
        if (!isset($data['impounding'])) {
            return null;
        }

        return Qry::create(['id' => $data['impounding'], 'bundle' => []]);
    }

    /**
     * Renders the bookmark
     *
     * @return string
     */
    public function render()
    {
        return $this->data['vrm'];
    }
}

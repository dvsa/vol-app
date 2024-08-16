<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\ImpoundingBundle as Qry;

/**
 * Impounding Hearing Venue
 */
class ImpoundingHearingVenue extends DynamicBookmark
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

        return Qry::create(['id' => $data['impounding'], 'bundle' => ['venue']]);
    }

    /**
     * Renders the bookmark
     *
     * @return string
     */
    public function render()
    {
        if (!empty($this->data['venue'])) {
            return $this->data['venue']['name'];
        }

        return $this->data['venueOther'];
    }
}

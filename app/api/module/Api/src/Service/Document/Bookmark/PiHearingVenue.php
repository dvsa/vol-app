<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PiHearingBundle as Qry;

/**
 * PiHearingVenue
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class PiHearingVenue extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        if (!isset($data['hearing'])) {
            return null;
        }

        return Qry::create(['id' => $data['hearing'], 'bundle' => ['venue']]);
    }

    public function render()
    {
        if (!empty($this->data['venue'])) {
            return $this->data['venue']['name'];
        }

        return $this->data['venueOther'];
    }
}

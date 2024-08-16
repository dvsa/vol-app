<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Licence vehicle small limit bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceVehicleSmallLimit extends DynamicBookmark
{
    public const EMPTY_AUTH = 'Total number (if any)';

    public function getQuery(array $data)
    {
        return Qry::create(
            [
                'id' => $data['licence'],
            ]
        );
    }

    public function render()
    {
        if (empty($this->data['totAuthSmallVehicles'])) {
            return self::EMPTY_AUTH;
        }
        return $this->data['totAuthSmallVehicles'];
    }
}

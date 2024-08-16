<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Licence vehicle medium limit bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceVehicleMediumLimit extends DynamicBookmark
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
        if (empty($this->data['totAuthMediumVehicles'])) {
            return self::EMPTY_AUTH;
        }
        return $this->data['totAuthMediumVehicles'];
    }
}

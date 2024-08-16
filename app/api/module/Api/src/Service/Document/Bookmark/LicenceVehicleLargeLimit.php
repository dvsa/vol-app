<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

/**
 * Licence vehicle large limit bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceVehicleLargeLimit extends DynamicBookmark
{
    public const EMPTY_AUTH = 'Total number (if any)';
    public const NA = 'N/A';

    public function getQuery(array $data)
    {
        return Qry::create(
            [
                'id' => $data['licence'],
                'bundle' => [
                    'licenceType'
                ]
            ]
        );
    }

    public function render()
    {
        if ($this->data['licenceType']['id'] === LicenceEntity::LICENCE_TYPE_RESTRICTED) {
            return self::NA;
        }
        if (empty($this->data['totAuthLargeVehicles'])) {
            return self::EMPTY_AUTH;
        }
        return $this->data['totAuthLargeVehicles'];
    }
}

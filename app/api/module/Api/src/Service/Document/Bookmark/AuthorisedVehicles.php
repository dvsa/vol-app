<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;
use Dvsa\Olcs\Api\Domain\TranslatorAwareInterface;
use Dvsa\Olcs\Api\Domain\TranslatorAwareTrait;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Licence - Total vehicle authority
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class AuthorisedVehicles extends DynamicBookmark implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    /**
     * Get the data required for the bookmark
     *
     * @return QueryInterface
     */
    public function getQuery(array $data): QueryInterface
    {
        return Qry::create(['id' => $data['licence']]);
    }

    /**
     * Render the bookmark
     *
     * @return string
     */
    public function render()
    {
        switch ($this->data['vehicleType']['id']) {
            case RefData::APP_VEHICLE_TYPE_MIXED:
                if ($this->data['totAuthLgvVehicles'] === null) {
                    return $this->data['totAuthHgvVehicles'];
                }
                return sprintf(
                    "%d Heavy goods vehicles\n\n%d Light goods vehicles\n\n%s",
                    $this->data['totAuthHgvVehicles'],
                    $this->data['totAuthLgvVehicles'],
                    $this->translate('light_goods_vehicle.undertakings.vehicle-bookmark')
                );
            case RefData::APP_VEHICLE_TYPE_LGV:
                return sprintf(
                    "%d Light goods vehicles\n\n%s",
                    $this->data['totAuthLgvVehicles'],
                    $this->translate('light_goods_vehicle.undertakings.vehicle-bookmark')
                );
            default:
                return $this->data['totAuthVehicles'];
        }
    }
}

<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Traffic Area (with phone number) Address bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TaAddressPhone extends DynamicBookmark
{
    /**
     * Get Query
     *
     * @param array $data Known data
     *
     * @return Qry
     */
    public function getQuery(array $data)
    {
        $bundle = [
            'trafficArea' => [
                'contactDetails' => [
                    'address',
                    'phoneContacts' => [
                        'phoneContactType'
                    ]
                ]
            ]
        ];
        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    /**
     * Render bookmark
     *
     * @return string
     */
    public function render()
    {
        $trafficArea = $this->data['trafficArea'];
        $contactDetails = $trafficArea['contactDetails'];
        $address = $contactDetails['address'] ?? [];

        return implode(
            "\n",
            array_filter(
                [
                    $trafficArea['name'],
                    Formatter\Address::format($address),
                    $this->fetchTelephone()
                ]
            )
        );
    }

    /**
     * Get contact number
     *
     * @return string
     */
    private function fetchTelephone()
    {
        if ($this->data['trafficArea']['id'] === TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE) {
            return '';
        }

        if (empty($this->data['trafficArea']['contactDetails']['phoneContacts'])) {
            return '';
        }

        return Formatter\ContactNumber::format($this->data['trafficArea']['contactDetails']['phoneContacts']);
    }
}

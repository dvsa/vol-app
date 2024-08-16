<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as LicenceQry;

/**
 * Traffic Area Address bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TaAddress extends DynamicBookmark
{
    /**
     * Get query
     *
     * @param array $data data
     *
     * @return LicenceQry
     */
    public function getQuery(array $data)
    {
        $licenceBundle = [
            'trafficArea' => [
                'contactDetails' => [
                    'address'
                ]
            ]
        ];
        return LicenceQry::create(['id' => $data['licence'], 'bundle' => $licenceBundle]);
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        $licence = $this->data;

        if (isset($licence['trafficArea']['contactDetails']['address'])) {
            $trafficArea = $licence['trafficArea']['name'];
            $address = $licence['trafficArea']['contactDetails']['address'];
        } else {
            $trafficArea = $licence['trafficArea']['name'] ?? '';
            $address = [];
        }

        return implode(
            "\n",
            array_filter(
                [
                    $trafficArea,
                    Formatter\Address::format($address)
                ]
            )
        );
    }
}

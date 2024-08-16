<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter\Address;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * List of operating centre addresses
 */
class SubjectOperatingCentreAddress extends DynamicBookmark
{
    /**
     * Get the query to populate the bookmark
     *
     * @param array $data Data
     *
     * @return Qry
     */
    public function getQuery(array $data)
    {
        $bundle = [
            'operatingCentres' => [
                'operatingCentre' => [
                    'address'
                ]
            ],
        ];
        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    /**
     * Render the bookmark
     *
     * @return string
     */
    public function render()
    {
        if (empty($this->data['operatingCentres'])) {
            return '';
        }

        Address::resetToDefault();

        $operatingCentres = [];
        foreach ($this->data['operatingCentres'] as $operatingCentre) {
            Address::setSeparator(', ');
            $operatingCentres[] = Address::format($operatingCentre['operatingCentre']['address']);
        }

        return implode("\n", $operatingCentres);
    }
}

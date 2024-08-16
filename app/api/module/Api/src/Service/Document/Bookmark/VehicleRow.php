<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Vehicle row bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehicleRow extends DynamicBookmark
{
    /**
     * Let the parser know we've already formatted our content by the
     * time it has been rendered
     */
    public const PREFORMATTED = true;

    public function getQuery(array $data)
    {
        $bundle = [
            'licenceVehicles' => [
                'vehicle'
            ]
        ];
        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    public function render()
    {
        if (!isset($this->data['licenceVehicles'])) {
            return '';
        }

        $vehicles = $this->data['licenceVehicles'];

        $snippet = $this->getSnippet();
        $parser  = $this->getParser();

        $str = '';

        foreach ($vehicles as $vehicle) {
            // ignore any vehicles marked as having been removed
            if ($vehicle['removalDate'] !== null) {
                continue;
            }

            // ignore any vehicles that are not specified
            if (is_null($vehicle['specifiedDate'])) {
                 continue;
            }

            $tokens = [
                'SPEC_DATE'     => date('d-M-Y', strtotime((string) $vehicle['specifiedDate'])),
                'PLATED_WEIGHT' => $vehicle['vehicle']['platedWeight'],
                'REG_MARK'      => $vehicle['vehicle']['vrm']
            ];
            $str .= $parser->replace($snippet, $tokens);
        }
        return $str;
    }
}

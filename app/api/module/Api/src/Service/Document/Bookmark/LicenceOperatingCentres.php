<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * LicenceOperatingCentres bookmark
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceOperatingCentres extends DynamicBookmark
{
    public function getQuery(array $data)
    {
        $bundle = [
            'operatingCentres' => [
                'operatingCentre' => [
                    'address'
                ]
            ],
            'goodsOrPsv'
        ];
        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    public function render()
    {
        if (empty($this->data)) {
            return '';
        }
        $isGoods = $this->data['goodsOrPsv']['id'] === Licence::LICENCE_CATEGORY_GOODS_VEHICLE;

        $header = [[
            'BOOKMARK1' => 'Operating centre address (insert full postcode if not shown)',
            'BOOKMARK2' => $isGoods ? 'Vehicles/trailers authorised' : 'Vehicles authorised'
        ]];

        $rows = [];
        foreach ($this->data['operatingCentres'] as $childOc) {
            $oc = $childOc['operatingCentre'];
            $bookmark1 = Formatter\Address::format($oc['address']);
            if ($isGoods) {
                $bookmark2 = 'Maximum number of vehicles :  ' . $childOc['noOfVehiclesRequired'] . "\n" .
                    'Maximum number of trailers :  ' . $childOc['noOfTrailersRequired'];
            } else {
                $bookmark2 = 'Maximum number of vehicles :  ' . $childOc['noOfVehiclesRequired'];
            }
            $rows[] = [
                'BOOKMARK1' => $bookmark1,
                'BOOKMARK2' => $bookmark2
            ];
        }

        /*
         * sorting alphabetically by O/C address
         */
        usort(
            $rows,
            function ($a, $b) {
                if ($a['BOOKMARK1'] == $b['BOOKMARK1']) {
                    return 0;
                } elseif ($a['BOOKMARK1'] < $b['BOOKMARK1']) {
                    return -1;
                } else {
                    return 1;
                }
            }
        );

        $rows = array_pad($rows, 6, ['BOOKMARK1' => '', 'BOOKMARK2' => '']);

        $allRows = array_merge($header, $rows);

        $snippet = $this->getSnippet('CHECKLIST_2CELL_TABLE');
        $parser  = $this->getParser();

        $str = '';
        foreach ($allRows as $tokens) {
            $str .= $parser->replace($snippet, $tokens);
        }
        return $str;
    }
}

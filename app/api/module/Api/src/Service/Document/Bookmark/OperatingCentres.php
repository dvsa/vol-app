<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Operating Centres list bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OperatingCentres extends DynamicBookmark
{
    /**
     * Let the parser know we've already formatted our content by the
     * time it has been rendered
     */
    public const PREFORMATTED = true;

    public function getQuery(array $data)
    {
        $bundle = [
            'operatingCentres' => [
                'operatingCentre' => [
                    'address',
                    'conditionUndertakings' => [
                        'conditionType',
                        'attachedTo',
                        'licence'
                    ]
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
        $isMixedWithLgv = ($this->data['vehicleType']['id'] === RefData::APP_VEHICLE_TYPE_MIXED) && ($this->data['totAuthLgvVehicles'] !== null);
        $rows = [];

        foreach ($this->data['operatingCentres'] as $licenceOc) {
            $oc = $licenceOc['operatingCentre'];

            $conditionsUndertakings = Formatter\ConditionsUndertakings::format(
                $this->filterConditionsUndertakings($oc['conditionUndertakings'], $this->data['id'])
            );

            $rows[] = [
                'TAB_OC_ADD' => Formatter\Address::format($oc['address']),
                'TAB_VEH' => $isMixedWithLgv ? 'Heavy goods vehicles' : 'Vehicles',
                'TAB_OC_VEH' => $licenceOc['noOfVehiclesRequired'],
                'TAB_TRAILER' => $isGoods ? 'Trailers' : '',
                'TAB_OC_TRAILER' => $isGoods ? $licenceOc['noOfTrailersRequired'] : '',
                'TAB_OC_CONDS_UNDERS' => $conditionsUndertakings
            ];
        }

        $snippet = $this->getSnippet('OcTable');
        $parser  = $this->getParser();

        $str = '';
        foreach ($rows as $tokens) {
            $str .= $parser->replace($snippet, $tokens);
        }
        return $str;
    }

    private function filterConditionsUndertakings($input, $licenceId)
    {
        return array_filter(
            $input,
            fn($val) => $val['attachedTo']['id'] === ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE
            && $val['isFulfilled'] === 'N'
            && $val['isDraft'] === 'N'
            && isset($val['licence']['id'])
            && $val['licence']['id'] === $licenceId
        );
    }
}

<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\InterimOperatingCentres as Qry;

/**
 * Interim Operating Centres list bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class InterimOperatingCentres extends DynamicBookmark
{
    /**
     * Let the parser know we've already formatted our content by the
     * time it has been rendered
     */
    public const PREFORMATTED = true;

    public function getQuery(array $data)
    {
        return Qry::create(['id' => $data['application']]);
    }

    public function render()
    {
        if (empty($this->data)) {
            return '';
        }

        $isGoods = $this->data['goodsOrPsv']['id'] === Licence::LICENCE_CATEGORY_GOODS_VEHICLE;
        $isMixedWithLgv = ($this->data['vehicleType']['id'] === RefData::APP_VEHICLE_TYPE_MIXED) && ($this->data['totAuthLgvVehicles'] !== null);
        $rows = [];

        foreach ($this->data['operatingCentres'] as $childOc) {
            $oc = $childOc['operatingCentre'];

            $conditionsUndertakings = Formatter\ConditionsUndertakings::format(
                $this->filterConditionsUndertakings(
                    $oc['conditionUndertakings'],
                    $this->data['id'],
                    $this->data['licence']['id']
                )
            );

            $rows[] = [
                'TAB_OC_ADD' => Formatter\Address::format($oc['address']),
                'TAB_VEH' => $isMixedWithLgv ? 'Heavy goods vehicles' : 'Vehicles',
                'TAB_OC_VEH' => $childOc['noOfVehiclesRequired'],
                'TAB_TRAILER' => $isGoods ? 'Trailers' : '',
                'TAB_OC_TRAILER' => $isGoods ? $childOc['noOfTrailersRequired'] : '',
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

    private function filterConditionsUndertakings($input, $applicationId, $licenceId)
    {
        $combinedConditions = array_merge(
            $this->getIndexedData($input, 'licence'),
            $this->getIndexedData($input, 'application')
        );

        $conditions = [];
        foreach ($combinedConditions as $condition) {
            /**
             * We can't do this filtering at the DB level; if we did we'd miss delta updates
             * which could be relevant, i.e. a record which was fulfilled but isn't in the delta
             */
            if (
                $condition['isFulfilled'] === 'N'
                && $condition['attachedTo']['id'] === ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE
                && $condition['action'] !== 'D'
                && (
                    /**
                     * We can't filter by (licence = ? OR application = ?) in our query since
                     * we don't know the licence ID ahead of time; we only know it
                     * based on the application we get back
                     */
                    (isset($condition['licence']['id']) && $condition['licence']['id'] === $licenceId)
                    || (isset($condition['application']['id']) && $condition['application']['id'] === $applicationId)
                )
            ) {
                $conditions[] = $condition;
            }
        }

        return $conditions;
    }

    private function getIndexedData($input, $type)
    {
        $final = [];
        foreach ($input as $condition) {
            /**
             * Wrong type; bail early
             */
            if (!isset($condition[$type]['id'])) {
                continue;
            }

            if (isset($condition['licConditionVariation']['id'])) {
                $key = $condition['licConditionVariation']['id'];
            } else {
                $key = $condition['id'];
            }
            $final['index:' . $key] = $condition;
        }
        return $final;
    }
}

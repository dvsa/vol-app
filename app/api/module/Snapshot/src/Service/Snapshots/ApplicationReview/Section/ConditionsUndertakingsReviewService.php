<?php

/**
 * Conditions Undertakings Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;

/**
 * Conditions Undertakings Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsReviewService extends AbstractReviewService
{
    public function getConfigFromData(array $data = [])
    {
        return [];
    }

    public function formatLicenceSubSection($list, $lva, $conditionOrUndertaking, $action)
    {
        return [
            'title' => $lva . '-review-conditions-undertakings-licence-' . $conditionOrUndertaking . '-' . $action,
            'mainItems' => [
                [
                    'multiItems' => [
                        [
                            [
                                'list' => $this->formatConditionsList($list)
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function formatOcSubSection($list, $lva, $conditionOrUndertaking, $action)
    {
        $mainItems = [];

        foreach ($list as $conditions) {
            $mainItems[] = [
                'header' => $this->formatShortAddress($conditions[0]['operatingCentre']['address']),
                'multiItems' => [
                    [
                        [
                            'list' => $this->formatConditionsList($conditions)
                        ]
                    ]
                ]
            ];
        }

        return [
            'title' => $lva . '-review-conditions-undertakings-oc-' . $conditionOrUndertaking . '-' . $action,
            'mainItems' => $mainItems
        ];
    }

    /**
     * Flatten the conditions into a single dimension array
     *
     * @param array $conditions
     * @return array
     */
    public function formatConditionsList($conditions)
    {
        $list = [];

        foreach ($conditions as $condition) {
            $list[] = $condition['notes'];
        }

        return $list;
    }

    /**
     * Split all conditions and undertakings into 4 lists
     *  - Licence conditions
     *  - Licence undertakings
     *  - Operating centre conditions
     *  - Operating centre undertakings
     *
     * @param array $data
     * @param bool $filterByAction
     * @return array
     */
    public function splitUpConditionsAndUndertakings($data, $filterByAction = true)
    {
        $licConds = $licUnds = $ocConds = $ocUnds = [];

        foreach ($data['conditionUndertakings'] as $condition) {
            if ($filterByAction) {
                $index = $condition['action'];
            } else {
                $index = 'list';
            }

            // Decide which list to push onto
            switch (true) {
                case $this->isLicenceCondition($condition):
                    $licConds[$index][] = $condition;
                    break;
                case $this->isLicenceUndertaking($condition):
                    $licUnds[$index][] = $condition;
                    break;
                case $this->isOcCondition($condition):
                    $ocConds[$index][$condition['operatingCentre']['id']][] = $condition;
                    break;
                case $this->isOcUndertaking($condition):
                    $ocUnds[$index][$condition['operatingCentre']['id']][] = $condition;
            }
        }

        return [$licConds, $licUnds, $ocConds, $ocUnds];
    }

    protected function isLicenceCondition($condition)
    {
        return $condition['conditionType']['id'] === ConditionUndertaking::TYPE_CONDITION
            && $condition['attachedTo']['id'] === ConditionUndertaking::ATTACHED_TO_LICENCE;
    }

    protected function isLicenceUndertaking($condition)
    {
        return $condition['conditionType']['id'] === ConditionUndertaking::TYPE_UNDERTAKING
            && $condition['attachedTo']['id'] === ConditionUndertaking::ATTACHED_TO_LICENCE;
    }

    protected function isOcCondition($condition)
    {
        return $condition['conditionType']['id'] === ConditionUndertaking::TYPE_CONDITION
            && $condition['attachedTo']['id'] === ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE;
    }

    protected function isOcUndertaking($condition)
    {
        return $condition['conditionType']['id'] === ConditionUndertaking::TYPE_UNDERTAKING
            && $condition['attachedTo']['id'] === ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE;
    }
}

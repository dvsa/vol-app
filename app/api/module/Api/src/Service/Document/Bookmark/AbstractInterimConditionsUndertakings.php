<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\InterimConditionsUndertakings as Qry;

/**
 * Abstract Interim Conditions / Undertakings
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractInterimConditionsUndertakings extends DynamicBookmark
{
    public const CONDITION_TYPE = null;

    public function getQuery(array $data)
    {
        return Qry::create(
            [
                'id' => $data['application'],
                'conditionType' => static::CONDITION_TYPE,
                'attachedTo' => ConditionUndertaking::ATTACHED_TO_LICENCE
            ]
        );
    }

    public function render()
    {
        $combinedConditions = array_merge(
            $this->getIndexedData($this->data['licence']['conditionUndertakings']),
            $this->getIndexedData($this->data['conditionUndertakings'])
        );

        $conditions = [];
        foreach ($combinedConditions as $condition) {
            if (
                $condition['isFulfilled'] === 'N'
                && $condition['conditionType']['id'] === static::CONDITION_TYPE
                && $condition['attachedTo']['id'] === ConditionUndertaking::ATTACHED_TO_LICENCE
                && $condition['action'] !== 'D'
            ) {
                $conditions[] = $condition['notes'];
            }
        }

        return implode("\n\n", $conditions);
    }

    protected function getIndexedData($conditions)
    {
        $final = [];
        foreach ($conditions as $condition) {
            if (isset($condition['licConditionVariation']['id'])) {
                $key = $condition['licConditionVariation']['id'];
            } else {
                $key = $condition['id'];
            }
            $final["index:" . $key] = $condition;
        }

        return $final;
    }
}

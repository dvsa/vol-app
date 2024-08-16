<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\ConditionsUndertakings as Qry;

/**
 * Abstract Conditions / Undertakings
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractConditionsUndertakings extends DynamicBookmark
{
    public const CONDITION_TYPE = null;

    public function getQuery(array $data)
    {
        return Qry::create(
            [
                'id' => $data['licence'],
                'attachedTo' => ConditionUndertaking::ATTACHED_TO_LICENCE,
                'conditionType' => static::CONDITION_TYPE
            ]
        );
    }

    public function render()
    {
        return implode(
            "\n\n",
            array_map(
                fn($v) => $v['notes'],
                $this->data['conditionUndertakings']
            )
        );
    }
}

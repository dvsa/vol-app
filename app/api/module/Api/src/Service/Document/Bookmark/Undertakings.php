<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;

/**
 * Licence - Undertakings
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class Undertakings extends AbstractConditionsUndertakings
{
    public const CONDITION_TYPE = ConditionUndertaking::TYPE_UNDERTAKING;
}

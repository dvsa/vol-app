<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;

/**
 * Licence - Interim Conditions
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class InterimSpecificLicenceConditions extends AbstractInterimConditionsUndertakings
{
    public const CONDITION_TYPE = ConditionUndertaking::TYPE_CONDITION;
}

<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;

/**
 * Licence - Interim Undertakings
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class InterimSpecificLicenceUndertakings extends AbstractInterimConditionsUndertakings
{
    public const CONDITION_TYPE = ConditionUndertaking::TYPE_UNDERTAKING;
}

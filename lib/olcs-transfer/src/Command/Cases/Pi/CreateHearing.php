<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Pi;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/pi/hearing")
 * @Transfer\Method("POST")
 */
class CreateHearing extends AbstractCommand
{
    use FieldType\Pi;
    use FieldType\VenueOptional;
    use FieldType\VenueOtherOptional;
    use FieldType\IsFullDay;
    use FieldType\PresidingTC;
    use FieldType\PresidedByRole;
    use FieldType\Witnesses;
    use FieldType\Drivers;
    use FieldType\HearingDate;
    use FieldType\IsCancelled;
    use FieldType\CancelledDateOptional;
    use FieldType\CancelledReasonOptional;
    use FieldType\IsAdjourned;
    use FieldType\AdjournedDateOptional;
    use FieldType\AdjournedReasonOptional;
    use FieldType\TrafficAreasOptional;
    use FieldType\PubTypeOptional;
    use FieldType\DetailsOptional;
    use FieldType\Publish;
}

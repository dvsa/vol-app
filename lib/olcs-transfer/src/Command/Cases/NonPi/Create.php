<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\NonPi;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * @Transfer\RouteName("backend/non-pi")
 * @Transfer\Method("POST")
 */
class Create extends AbstractCommand implements FieldType\CasesInterface
{
    use FieldType\Traits\Cases;
    use FieldType\Traits\HearingType;
    use FieldType\Traits\VenueOptional;
    use FieldType\Traits\PresidingStaffNameOptional;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $agreedByTcDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\DateTimeFormatter")
     * @Transfer\Validator("Date", options={"format": \DateTime::ISO8601})
     */
    protected $hearingDate;

    /**
     * @var string
     *
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    protected $venueOther;

    /**
     * @var int
     *
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Between",options={"min":0,"max":99,"inclusive":true})
     */
    protected $witnessCount;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack":{"non_pio_con", "non_pio_nfa", "non_pio_other", "non_pio_ph", "non_pio_pi", "non_pio_und", "non_pio_wl"}})
     */
    protected $outcome;

    /**
     * Get agreedByTc date
     *
     * @return string
     */
    public function getAgreedByTcDate()
    {
        return $this->agreedByTcDate;
    }

    /**
     * Get hearing date
     *
     * @return string
     */
    public function getHearingDate()
    {
        return $this->hearingDate;
    }

    /**
     * Get venue other
     *
     * @return string
     */
    public function getVenueOther()
    {
        return $this->venueOther;
    }

    /**
     * Get witness count
     *
     * @return int
     */
    public function getWitnessCount()
    {
        return $this->witnessCount;
    }

    /**
     * Get outcome
     *
     * @return string
     */
    public function getOutcome()
    {
        return $this->outcome;
    }
}

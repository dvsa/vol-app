<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterInstance;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\LetterTypeOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CasesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\BusRegOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\OrganisationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TransportManagerOptional;

/**
 * @Transfer\RouteName("backend/letter/letter-instance/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    use Identity;
    use LetterTypeOptional;
    use LicenceOptional;
    use ApplicationOptional;
    use CasesOptional;
    use BusRegOptional;
    use OrganisationOptional;
    use TransportManagerOptional;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":100})
     */
    protected $reference;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\DateTimeFormatter")
     * @Transfer\Validator("Laminas\Validator\Date", options={"format": "Y-m-d H:i:s"})
     */
    protected $sentOn;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\DateTimeFormatter")
     * @Transfer\Validator("Laminas\Validator\Date", options={"format": "Y-m-d H:i:s"})
     */
    protected $deletedDate;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $document;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $irfoOrganisation;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"letter_instance_status_draft", "letter_instance_status_sent", "letter_instance_status_failed"}})
     */
    protected $status;

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getSentOn()
    {
        return $this->sentOn;
    }

    /**
     * @return string
     */
    public function getDeletedDate()
    {
        return $this->deletedDate;
    }

    /**
     * @return int
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return int
     */
    public function getIrfoOrganisation()
    {
        return $this->irfoOrganisation;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}

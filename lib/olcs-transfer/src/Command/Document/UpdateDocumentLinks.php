<?php

/**
 * Update Document Links
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\BusRegOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CasesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\MessagingConversationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\MessagingMessageOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TransportManagerOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\SurrenderOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/document/single/links")
 * @Transfer\Method("PUT")
 */
final class UpdateDocumentLinks extends AbstractCommand
{
    use Identity;
    use ApplicationOptional;
    use BusRegOptional;
    use CasesOptional;
    use TransportManagerOptional;
    use LicenceOptional;
    use IrhpApplicationOptional;
    use SurrenderOptional;
    use MessagingConversationOptional;
    use MessagingMessageOptional;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $irfoOrganisation;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $submission;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $trafficArea;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $operatingCentre;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $statement;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $continuationDetail;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $hearing;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $opposition;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $complaint;

    /**
     * @return int
     */
    public function getIrfoOrganisation()
    {
        return $this->irfoOrganisation;
    }

    /**
     * @return int
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * @return int
     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * @return int
     */
    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }

    /**
     * @return mixed
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * @return mixed
     */
    public function getHearing()
    {
        return $this->hearing;
    }

    /**
     * @return mixed
     */
    public function getOpposition()
    {
        return $this->opposition;
    }

    /**
     * @return mixed
     */
    public function getComplaint()
    {
        return $this->complaint;
    }

    /**
     * @return mixed
     */
    public function getContinuationDetail()
    {
        return $this->continuationDetail;
    }
}

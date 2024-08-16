<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;

/**
 * Complaint Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="complaint",
 *    indexes={
 *        @ORM\Index(name="ix_complaint_complainant_contact_details_id", columns={"complainant_contact_details_id"}),
 *        @ORM\Index(name="ix_complaint_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_complaint_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_complaint_status", columns={"status"}),
 *        @ORM\Index(name="ix_complaint_complaint_type", columns={"complaint_type"}),
 *        @ORM\Index(name="ix_complaint_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_complaint_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class Complaint extends AbstractComplaint
{
    public const COMPLAIN_STATUS_CLOSED = 'ecst_closed';
    public const COMPLAIN_STATUS_OPEN = 'ecst_open';

    /**
     * Construct Complaint entity
     *
     * @param Cases          $case           Case
     * @param bool           $isCompliance   Is Compliance
     * @param RefData        $status         Status
     * @param \DateTime      $complaintDate  Compliance Date
     * @param ContactDetails $contactDetails Contact Details Entity
     *
     * @return void
     */
    public function __construct(
        Cases $case,
        $isCompliance,
        RefData $status,
        \DateTime $complaintDate,
        ContactDetails $contactDetails
    ) {
        parent::__construct();

        $this->setCase($case);
        $this->setIsCompliance($isCompliance);
        $this->setStatus($status);
        $this->setComplaintDate($complaintDate);
        $this->setComplainantContactDetails($contactDetails);
    }

    /**
     * Is Open
     *
     * @return bool
     */
    public function isOpen()
    {
        return ($this->getStatus() && $this->getStatus()->getId() === self::COMPLAIN_STATUS_OPEN);
    }

    /**
     * Is Environmental Complaint
     *
     * @return bool
     */
    public function isEnvironmentalComplaint()
    {
        return ($this->isCompliance === false);
    }

    /**
     * Populate Closed Date
     *
     * @return $this
     */
    public function populateClosedDate()
    {
        if ($this->isEnvironmentalComplaint()) {
            // set closed date based on status
            if (!$this->isOpen()) {
                if ($this->closedDate === null) {
                    $this->closedDate = new \DateTime();
                }
            } else {
                $this->closedDate = null;
            }
        }

        return $this;
    }
}

<?php

namespace Dvsa\Olcs\Api\Entity\Inspection;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * InspectionEmail Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="inspection_email",
 *    indexes={
 *        @ORM\Index(name="ix_inspection_email_inspection_request_id",
     *     columns={"inspection_request_id"})
 *    }
 * )
 */
abstract class AbstractInspectionEmail implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;

    /**
     * Email status
     *
     * @var string
     *
     * @ORM\Column(type="string", name="email_status", length=1, nullable=false)
     */
    protected $emailStatus;

    /**
     * Identifier - Id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Inspection request
     *
     * @var \Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest", fetch="LAZY")
     * @ORM\JoinColumn(name="inspection_request_id", referencedColumnName="id", nullable=false)
     */
    protected $inspectionRequest;

    /**
     * Message body
     *
     * @var string
     *
     * @ORM\Column(type="text", name="message_body", length=16777215, nullable=true)
     */
    protected $messageBody;

    /**
     * Processed
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="processed", nullable=false, options={"default": 0})
     */
    protected $processed = 0;

    /**
     * Received date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="received_date", nullable=false)
     */
    protected $receivedDate;

    /**
     * Sender email address
     *
     * @var string
     *
     * @ORM\Column(type="string", name="sender_email_address", length=255, nullable=true)
     */
    protected $senderEmailAddress;

    /**
     * Subject
     *
     * @var string
     *
     * @ORM\Column(type="string", name="subject", length=1024, nullable=false)
     */
    protected $subject;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Set the email status
     *
     * @param string $emailStatus new value being set
     *
     * @return InspectionEmail
     */
    public function setEmailStatus($emailStatus)
    {
        $this->emailStatus = $emailStatus;

        return $this;
    }

    /**
     * Get the email status
     *
     * @return string
     */
    public function getEmailStatus()
    {
        return $this->emailStatus;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return InspectionEmail
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the inspection request
     *
     * @param \Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest $inspectionRequest entity being set as the value
     *
     * @return InspectionEmail
     */
    public function setInspectionRequest($inspectionRequest)
    {
        $this->inspectionRequest = $inspectionRequest;

        return $this;
    }

    /**
     * Get the inspection request
     *
     * @return \Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest
     */
    public function getInspectionRequest()
    {
        return $this->inspectionRequest;
    }

    /**
     * Set the message body
     *
     * @param string $messageBody new value being set
     *
     * @return InspectionEmail
     */
    public function setMessageBody($messageBody)
    {
        $this->messageBody = $messageBody;

        return $this;
    }

    /**
     * Get the message body
     *
     * @return string
     */
    public function getMessageBody()
    {
        return $this->messageBody;
    }

    /**
     * Set the processed
     *
     * @param string $processed new value being set
     *
     * @return InspectionEmail
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;

        return $this;
    }

    /**
     * Get the processed
     *
     * @return string
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * Set the received date
     *
     * @param \DateTime $receivedDate new value being set
     *
     * @return InspectionEmail
     */
    public function setReceivedDate($receivedDate)
    {
        $this->receivedDate = $receivedDate;

        return $this;
    }

    /**
     * Get the received date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getReceivedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->receivedDate);
        }

        return $this->receivedDate;
    }

    /**
     * Set the sender email address
     *
     * @param string $senderEmailAddress new value being set
     *
     * @return InspectionEmail
     */
    public function setSenderEmailAddress($senderEmailAddress)
    {
        $this->senderEmailAddress = $senderEmailAddress;

        return $this;
    }

    /**
     * Get the sender email address
     *
     * @return string
     */
    public function getSenderEmailAddress()
    {
        return $this->senderEmailAddress;
    }

    /**
     * Set the subject
     *
     * @param string $subject new value being set
     *
     * @return InspectionEmail
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return InspectionEmail
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}

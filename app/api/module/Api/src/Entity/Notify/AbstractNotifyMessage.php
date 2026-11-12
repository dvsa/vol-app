<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Notify;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractNotifyMessage Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 */
#[ORM\Table(name: 'notify_message')]
#[ORM\Index(name: 'ix_notify_message_application_id', columns: ['application_id'])]
#[ORM\Index(name: 'ix_notify_message_bus_reg_id', columns: ['bus_reg_id'])]
#[ORM\Index(name: 'ix_notify_message_case_id', columns: ['case_id'])]
#[ORM\Index(name: 'ix_notify_message_channel', columns: ['channel'])]
#[ORM\Index(name: 'ix_notify_message_channel_recipient_requested_on', columns: ['channel', 'recipient', 'requested_on'])]
#[ORM\Index(name: 'ix_notify_message_correspondence_inbox_id', columns: ['correspondence_inbox_id'])]
#[ORM\Index(name: 'ix_notify_message_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_notify_message_document_id', columns: ['document_id'])]
#[ORM\Index(name: 'ix_notify_message_irhp_application_id', columns: ['irhp_application_id'])]
#[ORM\Index(name: 'ix_notify_message_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Index(name: 'ix_notify_message_licence_id', columns: ['licence_id'])]
#[ORM\Index(name: 'ix_notify_message_organisation_id', columns: ['organisation_id'])]
#[ORM\Index(name: 'ix_notify_message_status', columns: ['status'])]
#[ORM\Index(name: 'ix_notify_message_transport_manager_id', columns: ['transport_manager_id'])]
#[ORM\Index(name: 'ix_notify_message_user_id', columns: ['user_id'])]
#[ORM\UniqueConstraint(name: 'uk_notify_message_notify_id', columns: ['notify_id'])]
#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractNotifyMessage implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Primary key.  Auto incremented if numeric.
     *
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'id', nullable: false, options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * ref_data notify_message_channel
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     */
    #[ORM\JoinColumn(name: 'channel', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\System\RefData::class, fetch: 'LAZY')]
    protected $channel;

    /**
     * ref_data notify_message_status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     */
    #[ORM\JoinColumn(name: 'status', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\System\RefData::class, fetch: 'LAZY')]
    protected $status;

    /**
     * Email address, phone number or postal address. PII.
     *
     * @var string
     */
    #[ORM\Column(type: 'string', name: 'recipient', length: 255, nullable: false)]
    protected $recipient = '';

    /**
     * Where the message actually went when send-all-to is configured
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', name: 'redirected_to', length: 255, nullable: true)]
    protected $redirectedTo;

    /**
     * Notification id returned by GOV.UK Notify
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', name: 'notify_id', length: 36, nullable: true, options: ['fixed' => true])]
    protected $notifyId;

    /**
     * Notify template id
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', name: 'notify_template_id', length: 36, nullable: true, options: ['fixed' => true])]
    protected $notifyTemplateId;

    /**
     * Template name
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', name: 'template_name', length: 100, nullable: true)]
    protected $templateName;

    /**
     * Description
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', name: 'description', length: 255, nullable: true)]
    protected $description;

    /**
     * Organisation
     *
     * @var \Dvsa\Olcs\Api\Entity\Organisation\Organisation|null
     */
    #[ORM\JoinColumn(name: 'organisation_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Organisation\Organisation::class, fetch: 'LAZY')]
    protected $organisation;

    /**
     * Licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence|null
     */
    #[ORM\JoinColumn(name: 'licence_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Licence\Licence::class, fetch: 'LAZY')]
    protected $licence;

    /**
     * Application
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application|null
     */
    #[ORM\JoinColumn(name: 'application_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Application\Application::class, fetch: 'LAZY')]
    protected $application;

    /**
     * BusReg
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\BusReg|null
     */
    #[ORM\JoinColumn(name: 'bus_reg_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Bus\BusReg::class, fetch: 'LAZY')]
    protected $busReg;

    /**
     * Case
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases|null
     */
    #[ORM\JoinColumn(name: 'case_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Cases\Cases::class, fetch: 'LAZY')]
    protected $case;

    /**
     * TransportManager
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManager|null
     */
    #[ORM\JoinColumn(name: 'transport_manager_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Tm\TransportManager::class, fetch: 'LAZY')]
    protected $transportManager;

    /**
     * IrhpApplication
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication|null
     */
    #[ORM\JoinColumn(name: 'irhp_application_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication::class, fetch: 'LAZY')]
    protected $irhpApplication;

    /**
     * Document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document|null
     */
    #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Doc\Document::class, fetch: 'LAZY')]
    protected $document;

    /**
     * CorrespondenceInbox
     *
     * @var \Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox|null
     */
    #[ORM\JoinColumn(name: 'correspondence_inbox_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox::class, fetch: 'LAZY')]
    protected $correspondenceInbox;

    /**
     * User
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User|null
     */
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\User\User::class, fetch: 'LAZY')]
    protected $user;

    /**
     * Error code
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', name: 'error_code', length: 32, nullable: true)]
    protected $errorCode;

    /**
     * Error message
     *
     * @var string|null
     */
    #[ORM\Column(type: 'string', name: 'error_message', length: 1024, nullable: true)]
    protected $errorMessage;

    /**
     * Attempts
     *
     * @var int
     */
    #[ORM\Column(type: 'smallint', name: 'attempts', nullable: false, options: ['default' => 0, 'unsigned' => true])]
    protected $attempts = 0;

    /**
     * Requested on
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', name: 'requested_on', nullable: false)]
    protected $requestedOn;

    /**
     * Sent on
     *
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', name: 'sent_on', nullable: true)]
    protected $sentOn;

    /**
     * Completed on
     *
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', name: 'completed_on', nullable: true)]
    protected $completedOn;

    /**
     * Status updated on
     *
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', name: 'status_updated_on', nullable: true)]
    protected $statusUpdatedOn;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User|null
     */
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\User\User::class, fetch: 'LAZY')]
    #[Gedmo\Blameable(on: 'create')]
    protected $createdBy;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User|null
     */
    #[ORM\JoinColumn(name: 'last_modified_by', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\User\User::class, fetch: 'LAZY')]
    #[Gedmo\Blameable(on: 'update')]
    protected $lastModifiedBy;

    /**
     * Version
     *
     * @var int
     */
    #[ORM\Column(type: 'smallint', name: 'version', nullable: false, options: ['default' => 1, 'unsigned' => true])]
    #[ORM\Version]
    protected $version = 1;

    /**
     * Initialise the collections
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise collections
     */
    public function initCollections(): void
    {
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return static
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
     * Set the channel
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $channel new value being set
     *
     * @return static
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get the channel
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status new value being set
     *
     * @return static
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the recipient
     *
     * @param string $recipient new value being set
     *
     * @return static
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get the recipient
     *
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set the redirected to
     *
     * @param string $redirectedTo new value being set
     *
     * @return static
     */
    public function setRedirectedTo($redirectedTo)
    {
        $this->redirectedTo = $redirectedTo;

        return $this;
    }

    /**
     * Get the redirected to
     *
     * @return string
     */
    public function getRedirectedTo()
    {
        return $this->redirectedTo;
    }

    /**
     * Set the notify id
     *
     * @param string $notifyId new value being set
     *
     * @return static
     */
    public function setNotifyId($notifyId)
    {
        $this->notifyId = $notifyId;

        return $this;
    }

    /**
     * Get the notify id
     *
     * @return string
     */
    public function getNotifyId()
    {
        return $this->notifyId;
    }

    /**
     * Set the notify template id
     *
     * @param string $notifyTemplateId new value being set
     *
     * @return static
     */
    public function setNotifyTemplateId($notifyTemplateId)
    {
        $this->notifyTemplateId = $notifyTemplateId;

        return $this;
    }

    /**
     * Get the notify template id
     *
     * @return string
     */
    public function getNotifyTemplateId()
    {
        return $this->notifyTemplateId;
    }

    /**
     * Set the template name
     *
     * @param string $templateName new value being set
     *
     * @return static
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;

        return $this;
    }

    /**
     * Get the template name
     *
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return static
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the organisation
     *
     * @param \Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation new value being set
     *
     * @return static
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get the organisation
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence new value being set
     *
     * @return static
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set the application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $application new value being set
     *
     * @return static
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get the application
     *
     * @return \Dvsa\Olcs\Api\Entity\Application\Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the bus reg
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusReg $busReg new value being set
     *
     * @return static
     */
    public function setBusReg($busReg)
    {
        $this->busReg = $busReg;

        return $this;
    }

    /**
     * Get the bus reg
     *
     * @return \Dvsa\Olcs\Api\Entity\Bus\BusReg
     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case new value being set
     *
     * @return static
     */
    public function setCase($case)
    {
        $this->case = $case;

        return $this;
    }

    /**
     * Get the case
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Cases
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Set the transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager new value being set
     *
     * @return static
     */
    public function setTransportManager($transportManager)
    {
        $this->transportManager = $transportManager;

        return $this;
    }

    /**
     * Get the transport manager
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Set the irhp application
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication $irhpApplication new value being set
     *
     * @return static
     */
    public function setIrhpApplication($irhpApplication)
    {
        $this->irhpApplication = $irhpApplication;

        return $this;
    }

    /**
     * Get the irhp application
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication
     */
    public function getIrhpApplication()
    {
        return $this->irhpApplication;
    }

    /**
     * Set the document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $document new value being set
     *
     * @return static
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get the document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set the correspondence inbox
     *
     * @param \Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox $correspondenceInbox new value being set
     *
     * @return static
     */
    public function setCorrespondenceInbox($correspondenceInbox)
    {
        $this->correspondenceInbox = $correspondenceInbox;

        return $this;
    }

    /**
     * Get the correspondence inbox
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox
     */
    public function getCorrespondenceInbox()
    {
        return $this->correspondenceInbox;
    }

    /**
     * Set the user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $user new value being set
     *
     * @return static
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the error code
     *
     * @param string $errorCode new value being set
     *
     * @return static
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * Get the error code
     *
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Set the error message
     *
     * @param string $errorMessage new value being set
     *
     * @return static
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * Get the error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Set the attempts
     *
     * @param int $attempts new value being set
     *
     * @return static
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * Get the attempts
     *
     * @return int
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * Set the requested on
     *
     * @param \DateTime $requestedOn new value being set
     *
     * @return static
     */
    public function setRequestedOn($requestedOn)
    {
        $this->requestedOn = $requestedOn;

        return $this;
    }

    /**
     * Get the requested on
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getRequestedOn($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->requestedOn);
        }

        return $this->requestedOn;
    }

    /**
     * Set the sent on
     *
     * @param \DateTime $sentOn new value being set
     *
     * @return static
     */
    public function setSentOn($sentOn)
    {
        $this->sentOn = $sentOn;

        return $this;
    }

    /**
     * Get the sent on
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getSentOn($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->sentOn);
        }

        return $this->sentOn;
    }

    /**
     * Set the completed on
     *
     * @param \DateTime $completedOn new value being set
     *
     * @return static
     */
    public function setCompletedOn($completedOn)
    {
        $this->completedOn = $completedOn;

        return $this;
    }

    /**
     * Get the completed on
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getCompletedOn($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->completedOn);
        }

        return $this->completedOn;
    }

    /**
     * Set the status updated on
     *
     * @param \DateTime $statusUpdatedOn new value being set
     *
     * @return static
     */
    public function setStatusUpdatedOn($statusUpdatedOn)
    {
        $this->statusUpdatedOn = $statusUpdatedOn;

        return $this;
    }

    /**
     * Get the status updated on
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getStatusUpdatedOn($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->statusUpdatedOn);
        }

        return $this->statusUpdatedOn;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return static
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return static
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return static
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

    /**
     * Get bundle data
     */
    #[\Override]
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}

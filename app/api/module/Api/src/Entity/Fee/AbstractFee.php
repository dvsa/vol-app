<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Fee;

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
 * AbstractFee Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 */
#[ORM\Table(name: 'fee')]
#[ORM\Index(name: 'ix_fee_application_id', columns: ['application_id'])]
#[ORM\Index(name: 'ix_fee_bus_reg_id', columns: ['bus_reg_id'])]
#[ORM\Index(name: 'ix_fee_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_fee_fee_status', columns: ['fee_status'])]
#[ORM\Index(name: 'ix_fee_fee_type_id', columns: ['fee_type_id'])]
#[ORM\Index(name: 'ix_fee_irfo_gv_permit_id', columns: ['irfo_gv_permit_id'])]
#[ORM\Index(name: 'ix_fee_irfo_psv_auth_id', columns: ['irfo_psv_auth_id'])]
#[ORM\Index(name: 'ix_fee_irhp_application_id', columns: ['irhp_application_id'])]
#[ORM\Index(name: 'ix_fee_irhp_permit_application_id', columns: ['irhp_permit_application_id'])]
#[ORM\Index(name: 'ix_fee_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Index(name: 'ix_fee_licence_id', columns: ['licence_id'])]
#[ORM\Index(name: 'ix_fee_parent_fee_id', columns: ['parent_fee_id'])]
#[ORM\Index(name: 'ix_fee_task_id', columns: ['task_id'])]
#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractFee implements BundleSerializableInterface, JsonSerializable, \Stringable
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
    #[ORM\Column(type: 'integer', name: 'id', nullable: false)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * Foreign Key to fee_type
     *
     * @var \Dvsa\Olcs\Api\Entity\Fee\FeeType
     */
    #[ORM\JoinColumn(name: 'fee_type_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Fee\FeeType::class, fetch: 'LAZY')]
    protected $feeType;

    /**
     * FeeStatus
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     */
    #[ORM\JoinColumn(name: 'fee_status', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\System\RefData::class, fetch: 'LAZY')]
    protected $feeStatus;

    /**
     * ParentFee
     *
     * @var \Dvsa\Olcs\Api\Entity\Fee\Fee
     */
    #[ORM\JoinColumn(name: 'parent_fee_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Fee\Fee::class, fetch: 'LAZY')]
    protected $parentFee;

    /**
     * Foreign Key to application
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     */
    #[ORM\JoinColumn(name: 'application_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Application\Application::class, fetch: 'LAZY')]
    protected $application;

    /**
     * Foreign Key to bus_reg
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\BusReg
     */
    #[ORM\JoinColumn(name: 'bus_reg_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Bus\BusReg::class, fetch: 'LAZY')]
    protected $busReg;

    /**
     * Foreign Key to licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     */
    #[ORM\JoinColumn(name: 'licence_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Licence\Licence::class, fetch: 'LAZY')]
    protected $licence;

    /**
     * Foreign Key to task
     *
     * @var \Dvsa\Olcs\Api\Entity\Task\Task
     */
    #[ORM\JoinColumn(name: 'task_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Task\Task::class, fetch: 'LAZY')]
    protected $task;

    /**
     * IrhpApplication
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication
     */
    #[ORM\JoinColumn(name: 'irhp_application_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication::class, fetch: 'LAZY')]
    protected $irhpApplication;

    /**
     * IrhpPermitApplication
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication
     */
    #[ORM\JoinColumn(name: 'irhp_permit_application_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication::class, fetch: 'LAZY')]
    protected $irhpPermitApplication;

    /**
     * Foreign Key to irfo_gv_permit
     *
     * @var \Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit
     */
    #[ORM\JoinColumn(name: 'irfo_gv_permit_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit::class, fetch: 'LAZY')]
    protected $irfoGvPermit;

    /**
     * Foreign Key to irfo_psv_auth
     *
     * @var \Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth
     */
    #[ORM\JoinColumn(name: 'irfo_psv_auth_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth::class, fetch: 'LAZY')]
    protected $irfoPsvAuth;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     */
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\User\User::class, fetch: 'LAZY')]
    #[Gedmo\Blameable(on: 'create')]
    protected $createdBy;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     */
    #[ORM\JoinColumn(name: 'last_modified_by', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\User\User::class, fetch: 'LAZY')]
    #[Gedmo\Blameable(on: 'update')]
    protected $lastModifiedBy;

    /**
     * Net amount
     *
     * @var string
     */
    #[ORM\Column(type: 'decimal', name: 'net_amount', nullable: false)]
    protected $netAmount = '';

    /**
     * Gross amount
     *
     * @var string
     */
    #[ORM\Column(type: 'decimal', name: 'gross_amount', nullable: false)]
    protected $grossAmount = '';

    /**
     * Vat amount
     *
     * @var string
     */
    #[ORM\Column(type: 'decimal', name: 'vat_amount', nullable: false, options: ['default' => '0.00'])]
    protected $vatAmount = 0.00;

    /**
     * Invoice line no
     *
     * @var int
     */
    #[ORM\Column(type: 'smallint', name: 'invoice_line_no', nullable: false, options: ['default' => 1])]
    protected $invoiceLineNo = 1;

    /**
     * Invoiced date
     *
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', name: 'invoiced_date', nullable: true)]
    protected $invoicedDate;

    /**
     * Description
     *
     * @var string
     */
    #[ORM\Column(type: 'string', name: 'description', length: 255, nullable: true)]
    protected $description;

    /**
     * irfoFeeExempt
     *
     * @var string
     */
    #[ORM\Column(type: 'yesnonull', name: 'irfo_fee_exempt', nullable: true)]
    protected $irfoFeeExempt;

    /**
     * Version
     *
     * @var int
     */
    #[ORM\Column(type: 'smallint', name: 'version', nullable: false, options: ['default' => 1])]
    #[ORM\Version]
    protected $version = 1;

    /**
     * FeeTransactions
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: \Dvsa\Olcs\Api\Entity\Fee\FeeTransaction::class, mappedBy: 'fee', cascade: ['persist'])]
    protected $feeTransactions;

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
        $this->feeTransactions = new ArrayCollection();
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
     * Set the fee type
     *
     * @param \Dvsa\Olcs\Api\Entity\Fee\FeeType $feeType new value being set
     *
     * @return static
     */
    public function setFeeType($feeType)
    {
        $this->feeType = $feeType;

        return $this;
    }

    /**
     * Get the fee type
     *
     * @return \Dvsa\Olcs\Api\Entity\Fee\FeeType
     */
    public function getFeeType()
    {
        return $this->feeType;
    }

    /**
     * Set the fee status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $feeStatus new value being set
     *
     * @return static
     */
    public function setFeeStatus($feeStatus)
    {
        $this->feeStatus = $feeStatus;

        return $this;
    }

    /**
     * Get the fee status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getFeeStatus()
    {
        return $this->feeStatus;
    }

    /**
     * Set the parent fee
     *
     * @param \Dvsa\Olcs\Api\Entity\Fee\Fee $parentFee new value being set
     *
     * @return static
     */
    public function setParentFee($parentFee)
    {
        $this->parentFee = $parentFee;

        return $this;
    }

    /**
     * Get the parent fee
     *
     * @return \Dvsa\Olcs\Api\Entity\Fee\Fee
     */
    public function getParentFee()
    {
        return $this->parentFee;
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
     * Set the task
     *
     * @param \Dvsa\Olcs\Api\Entity\Task\Task $task new value being set
     *
     * @return static
     */
    public function setTask($task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get the task
     *
     * @return \Dvsa\Olcs\Api\Entity\Task\Task
     */
    public function getTask()
    {
        return $this->task;
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
     * Set the irhp permit application
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication $irhpPermitApplication new value being set
     *
     * @return static
     */
    public function setIrhpPermitApplication($irhpPermitApplication)
    {
        $this->irhpPermitApplication = $irhpPermitApplication;

        return $this;
    }

    /**
     * Get the irhp permit application
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication
     */
    public function getIrhpPermitApplication()
    {
        return $this->irhpPermitApplication;
    }

    /**
     * Set the irfo gv permit
     *
     * @param \Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit $irfoGvPermit new value being set
     *
     * @return static
     */
    public function setIrfoGvPermit($irfoGvPermit)
    {
        $this->irfoGvPermit = $irfoGvPermit;

        return $this;
    }

    /**
     * Get the irfo gv permit
     *
     * @return \Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit
     */
    public function getIrfoGvPermit()
    {
        return $this->irfoGvPermit;
    }

    /**
     * Set the irfo psv auth
     *
     * @param \Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth $irfoPsvAuth new value being set
     *
     * @return static
     */
    public function setIrfoPsvAuth($irfoPsvAuth)
    {
        $this->irfoPsvAuth = $irfoPsvAuth;

        return $this;
    }

    /**
     * Get the irfo psv auth
     *
     * @return \Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth
     */
    public function getIrfoPsvAuth()
    {
        return $this->irfoPsvAuth;
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
     * Set the net amount
     *
     * @param string $netAmount new value being set
     *
     * @return static
     */
    public function setNetAmount($netAmount)
    {
        $this->netAmount = $netAmount;

        return $this;
    }

    /**
     * Get the net amount
     *
     * @return string
     */
    public function getNetAmount()
    {
        return $this->netAmount;
    }

    /**
     * Set the gross amount
     *
     * @param string $grossAmount new value being set
     *
     * @return static
     */
    public function setGrossAmount($grossAmount)
    {
        $this->grossAmount = $grossAmount;

        return $this;
    }

    /**
     * Get the gross amount
     *
     * @return string
     */
    public function getGrossAmount()
    {
        return $this->grossAmount;
    }

    /**
     * Set the vat amount
     *
     * @param string $vatAmount new value being set
     *
     * @return static
     */
    public function setVatAmount($vatAmount)
    {
        $this->vatAmount = $vatAmount;

        return $this;
    }

    /**
     * Get the vat amount
     *
     * @return string
     */
    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    /**
     * Set the invoice line no
     *
     * @param int $invoiceLineNo new value being set
     *
     * @return static
     */
    public function setInvoiceLineNo($invoiceLineNo)
    {
        $this->invoiceLineNo = $invoiceLineNo;

        return $this;
    }

    /**
     * Get the invoice line no
     *
     * @return int
     */
    public function getInvoiceLineNo()
    {
        return $this->invoiceLineNo;
    }

    /**
     * Set the invoiced date
     *
     * @param \DateTime $invoicedDate new value being set
     *
     * @return static
     */
    public function setInvoicedDate($invoicedDate)
    {
        $this->invoicedDate = $invoicedDate;

        return $this;
    }

    /**
     * Get the invoiced date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime
     */
    public function getInvoicedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->invoicedDate);
        }

        return $this->invoicedDate;
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
     * Set the irfo fee exempt
     *
     * @param string $irfoFeeExempt new value being set
     *
     * @return static
     */
    public function setIrfoFeeExempt($irfoFeeExempt)
    {
        $this->irfoFeeExempt = $irfoFeeExempt;

        return $this;
    }

    /**
     * Get the irfo fee exempt
     *
     * @return string
     */
    public function getIrfoFeeExempt()
    {
        return $this->irfoFeeExempt;
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
     * Set the fee transactions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $feeTransactions collection being set as the value
     *
     * @return static
     */
    public function setFeeTransactions($feeTransactions)
    {
        $this->feeTransactions = $feeTransactions;

        return $this;
    }

    /**
     * Get the fee transactions
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFeeTransactions()
    {
        return $this->feeTransactions;
    }

    /**
     * Add a fee transactions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $feeTransactions collection being added
     *
     * @return static
     */
    public function addFeeTransactions($feeTransactions)
    {
        if ($feeTransactions instanceof ArrayCollection) {
            $this->feeTransactions = new ArrayCollection(
                array_merge(
                    $this->feeTransactions->toArray(),
                    $feeTransactions->toArray()
                )
            );
        } elseif (!$this->feeTransactions->contains($feeTransactions)) {
            $this->feeTransactions->add($feeTransactions);
        }

        return $this;
    }

    /**
     * Remove a fee transactions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $feeTransactions collection being removed
     *
     * @return static
     */
    public function removeFeeTransactions($feeTransactions)
    {
        if ($this->feeTransactions->contains($feeTransactions)) {
            $this->feeTransactions->removeElement($feeTransactions);
        }

        return $this;
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

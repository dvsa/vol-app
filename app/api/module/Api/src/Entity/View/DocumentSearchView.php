<?php

namespace Dvsa\Olcs\Api\Entity\View;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Document Search View
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="document_search_view")
 */
class DocumentSearchView implements BundleSerializableInterface
{
    use BundleSerializableTrait;

    public const IDENTIFIER_UNLINKED = 'Unlinked';

    /**
     * Id
     *
     * @var int
     *
     * NOTE: The ID annotation here is to allow doctrine to create the table (Even though we remove it later)
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     */
    protected $id;

    /**
     * Issued date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="issued_date")
     */
    protected $issuedDate;

    /**
     * Category ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="category_id")
     */
    protected $category;

    /**
     * Sub Category ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="sub_category_id")
     */
    protected $documentSubCategory;

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description")
     */
    protected $description;

    /**
     * Document Store Identifier
     *
     * @var string
     *
     * @ORM\Column(type="string", name="document_store_id")
     */
    protected $documentStoreIdentifier;

    /**
     * Document ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="document_id")
     */
    protected $document;

    /**
     * Category Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="category_name")
     */
    protected $categoryName;

    /**
     * Sub Category Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="document_sub_category_name")
     */
    protected $documentSubCategoryName;

    /**
     * Filename
     *
     * @var string
     *
     * @ORM\Column(type="string", name="filename")
     */
    protected $filename;

    /**
     * Is digital or not
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_external")
     */
    protected $isExternal;

    /**
     * Identifier
     *
     * @var string
     *
     * @ORM\Column(type="string", name="id_col")
     */
    protected $identifier;

    /**
     * Application Id
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="application_id")
     */
    protected $applicationId;

    /**
     * Licence number
     *
     * @var string
     *
     * @ORM\Column(type="string", name="lic_no")
     */
    protected $licenceNo;

    /**
     * Licence ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="licence_id")
     */
    protected $licenceId;

    /**
     * Family name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="family_name")
     */
    protected $familyName;

    /**
     * Case ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="case_id")
     */
    protected $caseId;

    /**
     * Bus Registration ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="bus_reg_id")
     */
    protected $busRegId;

    /**
     * Transport Manager ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="tm_id")
     */
    protected $tmId;

    /**
     * Correspondence Inbox ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="ci_id")
     */
    protected $ciId;

    /**
     * Organisation ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="irfo_organisation_id")
     */
    protected $irfoOrganisationId;

    /**
     * IRHP Application ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="irhp_application_id")
     */
    protected $irhpApplicationId;

    /**
     * Deleted date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="deleted_date", nullable=true)
     */
    protected $deletedDate;

    /**
     * Agreed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="agreed_date", nullable=true)
     */
    protected $agreedDate;

    /**
     * Target date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="target_date", nullable=true)
     */
    protected $targetDate;

    /**
     * Sent date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="sent_date", nullable=true)
     */
    protected $sentDate;

    /**
     * Format (ie file extension)
     *
     * @var string
     *
     * @ORM\Column(type="string", name="extension")
     */
    protected $extension;

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
     * Get the action date
     *
     * @return \DateTime
     */
    public function getIssuedDate()
    {
        return $this->issuedDate;
    }

    /**
     * Get the category ID
     *
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Get the sub category ID
     *
     * @return int
     */
    public function getDocumentSubCategory()
    {
        return $this->documentSubCategory;
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
     * Get the document store identifier
     *
     * @return string
     */
    public function getDocumentStoreIdentifier()
    {
        return $this->documentStoreIdentifier;
    }

    /**
     * Get the document ID
     *
     * @return int
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Get the category name
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * Get the sub category name
     *
     * @return string
     */
    public function getDocumentSubCategoryName()
    {
        return $this->documentSubCategoryName;
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get is external
     *
     * @return int
     */
    public function getIsExternal()
    {
        return $this->isExternal;
    }

    /**
     * Get the identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Get the application Identifier (if applicable)
     *
     * @return int
     */
    public function getApplicationId()
    {
        return $this->applicationId;
    }

    /**
     * Get the licence number
     *
     * @return int
     */
    public function getLicenceNo()
    {
        return $this->licenceNo;
    }

    /**
     * Get the licence ID (if applicable)
     *
     * @return int
     */
    public function getLicenceId()
    {
        return $this->licenceId;
    }

    /**
     * Get the family name
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * Get the case ID (if applicable)
     *
     * @return int
     */
    public function getCaseId()
    {
        return $this->caseId;
    }

    /**
     * Get the Bus Registration ID (if applicable)
     *
     * @return int
     */
    public function getBusRegId()
    {
        return $this->busRegId;
    }

    /**
     * Get the Transport Manager ID (if applicable)
     *
     * @return int
     */
    public function getTmId()
    {
        return $this->tmId;
    }

    /**
     * Get the Correspondence Inbox ID (if applicable)
     *
     * @return int
     */
    public function getCiId()
    {
        return $this->ciId;
    }

    /**
     * Get the IRFO Organisation ID (if applicable)
     *
     * @return int
     */
    public function getIrfoOrganisationId()
    {
        return $this->irfoOrganisationId;
    }

    /**
     * Get the IRHP Application ID (if applicable)
     *
     * @return int
     */
    public function getIrhpApplicationId()
    {
        return $this->irhpApplicationId;
    }

    /**
     * Set the deleted date
     *
     * @param \DateTime $deletedDate deleted date
     *
     * @return DocumentSearchView
     */
    public function setDeletedDate($deletedDate)
    {
        $this->deletedDate = $deletedDate;

        return $this;
    }

    /**
     * Get the deleted date
     *
     * @return \DateTime
     */
    public function getDeletedDate()
    {
        return $this->deletedDate;
    }

    /**
     * Is Deleted
     *
     * @return bool
     */
    public function isDeleted()
    {
        return !is_null($this->deletedDate);
    }

    /**
     * Get the agreed date
     *
     * @return \DateTime
     */
    public function getAgreedDate()
    {
        return $this->agreedDate;
    }

    /**
     * Get the target date
     *
     * @return \DateTime
     */
    public function getTargetDate()
    {
        return $this->targetDate;
    }

    /**
     * Get the sent date
     *
     * @return \DateTime
     */
    public function getSentDate()
    {
        return $this->sentDate;
    }

    /**
     * Get the extension of the file
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }
}

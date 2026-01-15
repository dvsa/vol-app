<?php

namespace Dvsa\Olcs\Api\Entity\View;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Doc Template Search View
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="doc_template_search_view")
 */
class DocTemplateSearchView implements BundleSerializableInterface
{
    use BundleSerializableTrait;

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
     * Last modified date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="last_modified_on")
     */
    protected $lastModifiedOn;

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
    protected $subCategory;

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
     * @ORM\Column(type="string", name="filename")
     */
    protected $filename;

    /**
     * Document ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="document_id")
     */
    protected $document;

    /**
     * Letter Type ID
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="letter_type_id", nullable=true)
     */
    protected $letterType;

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
     * @ORM\Column(type="string", name="sub_category_name")
     */
    protected $subCategoryName;

    /**
     * Letter Type Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="letter_type_name", nullable=true)
     */
    protected $letterTypeName;

    /**
     * Deleted date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="deleted_date", nullable=true)
     */
    protected $deletedDate;

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
     * Get the last modified date
     *
     * @return \DateTime
     */
    public function getLastModifiedOn()
    {
        return $this->lastModifiedOn;
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
    public function getSubCategory()
    {
        return $this->subCategory;
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
    public function getFilename()
    {
        return $this->filename;
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
    public function getSubCategoryName()
    {
        return $this->subCategoryName;
    }

    /**
     * Get the letter type ID
     *
     * @return int|null
     */
    public function getLetterType()
    {
        return $this->letterType;
    }

    /**
     * Get the letter type name
     *
     * @return string|null
     */
    public function getLetterTypeName()
    {
        return $this->letterTypeName;
    }

    /**
     * Set the deleted date
     *
     * @param \DateTime $deletedDate deleted date
     *
     * @return DocTemplateSearchView
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
}

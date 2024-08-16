<?php

namespace Dvsa\Olcs\Api\Entity\Doc;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * DocTemplate Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="doc_template",
 *    indexes={
 *        @ORM\Index(name="ix_doc_template_category_id", columns={"category_id"}),
 *        @ORM\Index(name="ix_doc_template_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_doc_template_document_id", columns={"document_id"}),
 *        @ORM\Index(name="ix_doc_template_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_doc_template_sub_category_id", columns={"sub_category_id"})
 *    }
 * )
 */
abstract class AbstractDocTemplate implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Category
     *
     * @var \Dvsa\Olcs\Api\Entity\System\Category
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\Category", fetch="LAZY")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     */
    protected $category;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="create")
     */
    protected $createdBy;

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=false)
     */
    protected $description;

    /**
     * Document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document",
     *     fetch="LAZY",
     *     inversedBy="templates"
     * )
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", nullable=false)
     */
    protected $document;

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
     * Is ni
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_ni", nullable=false, options={"default": 0})
     */
    protected $isNi = 0;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="update")
     */
    protected $lastModifiedBy;

    /**
     * Sub category
     *
     * @var \Dvsa\Olcs\Api\Entity\System\SubCategory
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\SubCategory", fetch="LAZY")
     * @ORM\JoinColumn(name="sub_category_id", referencedColumnName="id", nullable=true)
     */
    protected $subCategory;

    /**
     * Suppress from op
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="suppress_from_op", nullable=false)
     */
    protected $suppressFromOp;

    /**
     * Template slug
     *
     * @var string
     *
     * @ORM\Column(type="string", name="template_slug", length=100, nullable=true)
     */
    protected $templateSlug;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Doc template bookmark
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Doc\DocTemplateBookmark",
     *     mappedBy="docTemplate"
     * )
     */
    protected $docTemplateBookmarks;

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function initCollections()
    {
        $this->docTemplateBookmarks = new ArrayCollection();
    }

    /**
     * Set the category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\Category $category entity being set as the value
     *
     * @return DocTemplate
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get the category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return DocTemplate
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
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return DocTemplate
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
     * Set the document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $document entity being set as the value
     *
     * @return DocTemplate
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
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return DocTemplate
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
     * Set the is ni
     *
     * @param string $isNi new value being set
     *
     * @return DocTemplate
     */
    public function setIsNi($isNi)
    {
        $this->isNi = $isNi;

        return $this;
    }

    /**
     * Get the is ni
     *
     * @return string
     */
    public function getIsNi()
    {
        return $this->isNi;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return DocTemplate
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
     * Set the sub category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\SubCategory $subCategory entity being set as the value
     *
     * @return DocTemplate
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    /**
     * Get the sub category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\SubCategory
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * Set the suppress from op
     *
     * @param string $suppressFromOp new value being set
     *
     * @return DocTemplate
     */
    public function setSuppressFromOp($suppressFromOp)
    {
        $this->suppressFromOp = $suppressFromOp;

        return $this;
    }

    /**
     * Get the suppress from op
     *
     * @return string
     */
    public function getSuppressFromOp()
    {
        return $this->suppressFromOp;
    }

    /**
     * Set the template slug
     *
     * @param string $templateSlug new value being set
     *
     * @return DocTemplate
     */
    public function setTemplateSlug($templateSlug)
    {
        $this->templateSlug = $templateSlug;

        return $this;
    }

    /**
     * Get the template slug
     *
     * @return string
     */
    public function getTemplateSlug()
    {
        return $this->templateSlug;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return DocTemplate
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
     * Set the doc template bookmark
     *
     * @param ArrayCollection $docTemplateBookmarks collection being set as the value
     *
     * @return DocTemplate
     */
    public function setDocTemplateBookmarks($docTemplateBookmarks)
    {
        $this->docTemplateBookmarks = $docTemplateBookmarks;

        return $this;
    }

    /**
     * Get the doc template bookmarks
     *
     * @return ArrayCollection
     */
    public function getDocTemplateBookmarks()
    {
        return $this->docTemplateBookmarks;
    }

    /**
     * Add a doc template bookmarks
     *
     * @param ArrayCollection|mixed $docTemplateBookmarks collection being added
     *
     * @return DocTemplate
     */
    public function addDocTemplateBookmarks($docTemplateBookmarks)
    {
        if ($docTemplateBookmarks instanceof ArrayCollection) {
            $this->docTemplateBookmarks = new ArrayCollection(
                array_merge(
                    $this->docTemplateBookmarks->toArray(),
                    $docTemplateBookmarks->toArray()
                )
            );
        } elseif (!$this->docTemplateBookmarks->contains($docTemplateBookmarks)) {
            $this->docTemplateBookmarks->add($docTemplateBookmarks);
        }

        return $this;
    }

    /**
     * Remove a doc template bookmarks
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $docTemplateBookmarks collection being removed
     *
     * @return DocTemplate
     */
    public function removeDocTemplateBookmarks($docTemplateBookmarks)
    {
        if ($this->docTemplateBookmarks->contains($docTemplateBookmarks)) {
            $this->docTemplateBookmarks->removeElement($docTemplateBookmarks);
        }

        return $this;
    }
}

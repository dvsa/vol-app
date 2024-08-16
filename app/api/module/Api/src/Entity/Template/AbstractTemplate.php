<?php

namespace Dvsa\Olcs\Api\Entity\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Template Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="template",
 *    indexes={
 *        @ORM\Index(name="ix_template_category_id", columns={"category_id"}),
 *        @ORM\Index(name="ix_template_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_template_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_template_template_test_data_id", columns={"template_test_data_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="unique_name", columns={"locale","format","name"})
 *    }
 * )
 */
abstract class AbstractTemplate implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Category
     *
     * @var \Dvsa\Olcs\Api\Entity\System\Category
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\Category", fetch="LAZY")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true)
     */
    protected $category;

    /**
     * Category name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="category_name", length=40, nullable=true)
     */
    protected $categoryName;

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
     * Format
     *
     * @var string
     *
     * @ORM\Column(type="string", name="format", length=5, nullable=false)
     */
    protected $format;

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
     * Locale
     *
     * @var string
     *
     * @ORM\Column(type="string", name="locale", length=5, nullable=false)
     */
    protected $locale;

    /**
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=255, nullable=false)
     */
    protected $name;

    /**
     * Source
     *
     * @var string
     *
     * @ORM\Column(type="text", name="source", length=65535, nullable=false)
     */
    protected $source;

    /**
     * Template test data
     *
     * @var \Dvsa\Olcs\Api\Entity\Template\TemplateTestData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Template\TemplateTestData", fetch="LAZY")
     * @ORM\JoinColumn(name="template_test_data_id", referencedColumnName="id", nullable=false)
     */
    protected $templateTestData;

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
     * Set the category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\Category $category entity being set as the value
     *
     * @return Template
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
     * Set the category name
     *
     * @param string $categoryName new value being set
     *
     * @return Template
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;

        return $this;
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Template
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
     * @return Template
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
     * Set the format
     *
     * @param string $format new value being set
     *
     * @return Template
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get the format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Template
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
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return Template
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
     * Set the locale
     *
     * @param string $locale new value being set
     *
     * @return Template
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the name
     *
     * @param string $name new value being set
     *
     * @return Template
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the source
     *
     * @param string $source new value being set
     *
     * @return Template
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get the source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set the template test data
     *
     * @param \Dvsa\Olcs\Api\Entity\Template\TemplateTestData $templateTestData entity being set as the value
     *
     * @return Template
     */
    public function setTemplateTestData($templateTestData)
    {
        $this->templateTestData = $templateTestData;

        return $this;
    }

    /**
     * Get the template test data
     *
     * @return \Dvsa\Olcs\Api\Entity\Template\TemplateTestData
     */
    public function getTemplateTestData()
    {
        return $this->templateTestData;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Template
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

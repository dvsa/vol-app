<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

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
 * AbstractMasterTemplate Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 */
#[ORM\Table(name: 'master_template')]
#[ORM\Index(name: 'ix_master_template_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_master_template_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Index(name: 'ix_master_template_locale', columns: ['locale'])]
#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractMasterTemplate implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Name
     *
     * @var string
     */
    #[ORM\Column(type: 'string', name: 'name', length: 255, nullable: false)]
    protected $name = '';

    /**
     * HTML template with content placeholder
     *
     * @var string
     */
    #[ORM\Column(type: 'text', name: 'template_content', nullable: false)]
    protected $templateContent = '';

    /**
     * Is default
     *
     * @var bool
     */
    #[ORM\Column(type: 'boolean', name: 'is_default', nullable: false, options: ['default' => 0])]
    protected $isDefault = 0;

    /**
     * Locale / chrome variant key — extended vocabulary beyond strict ISO codes,
     * e.g. en_GB, en_NI, cy_GB, customN_GB, customN_NI. Picked at letter-generation
     * time by MasterTemplateResolver from the letter context (currently isNi).
     *
     * @var string
     */
    #[ORM\Column(type: 'string', name: 'locale', length: 20, nullable: true)]
    protected $locale;

    /**
     * EditorJS JSON for the top-left header slot (typically the logo). Replaces
     * the {{HEADER_LEFT_CONTENT}} placeholder in template_content at render time.
     *
     * @var array|null
     */
    #[ORM\Column(type: 'json', name: 'header_left_content', nullable: true)]
    protected $headerLeftContent;

    /**
     * EditorJS JSON for the top-right header slot (typically the address block).
     * Replaces the {{HEADER_RIGHT_CONTENT}} placeholder at render time.
     *
     * @var array|null
     */
    #[ORM\Column(type: 'json', name: 'header_right_content', nullable: true)]
    protected $headerRightContent;

    /**
     * EditorJS JSON for the signoff slot (typically "Yours, ..." + caseworker name).
     * Replaces the {{SIGNOFF_CONTENT}} placeholder at render time.
     *
     * @var array|null
     */
    #[ORM\Column(type: 'json', name: 'signoff_content', nullable: true)]
    protected $signoffContent;

    /**
     * EditorJS JSON for the footer slot (typically a single-line footer note).
     * Replaces the {{FOOTER_CONTENT}} placeholder at render time.
     *
     * @var array|null
     */
    #[ORM\Column(type: 'json', name: 'footer_content', nullable: true)]
    protected $footerContent;

    /**
     * Version
     *
     * @var int
     */
    #[ORM\Column(type: 'smallint', name: 'version', nullable: false, options: ['default' => 1])]
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
     * @return MasterTemplate
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return MasterTemplate
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
     * @return MasterTemplate
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
     * Set the name
     *
     * @param string $name new value being set
     *
     * @return MasterTemplate
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
     * Set the template content
     *
     * @param string $templateContent new value being set
     *
     * @return MasterTemplate
     */
    public function setTemplateContent($templateContent)
    {
        $this->templateContent = $templateContent;

        return $this;
    }

    /**
     * Get the template content
     *
     * @return string
     */
    public function getTemplateContent()
    {
        return $this->templateContent;
    }

    /**
     * Set the is default
     *
     * @param bool $isDefault new value being set
     *
     * @return MasterTemplate
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get the is default
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set the locale
     *
     * @param string $locale new value being set
     *
     * @return MasterTemplate
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
     * Set the header left content (EditorJS JSON)
     *
     * @param array|null $headerLeftContent
     *
     * @return MasterTemplate
     */
    public function setHeaderLeftContent($headerLeftContent)
    {
        $this->headerLeftContent = $headerLeftContent;

        return $this;
    }

    /**
     * Get the header left content (EditorJS JSON)
     *
     * @return array|null
     */
    public function getHeaderLeftContent()
    {
        return $this->headerLeftContent;
    }

    /**
     * Set the header right content (EditorJS JSON)
     *
     * @param array|null $headerRightContent
     *
     * @return MasterTemplate
     */
    public function setHeaderRightContent($headerRightContent)
    {
        $this->headerRightContent = $headerRightContent;

        return $this;
    }

    /**
     * Get the header right content (EditorJS JSON)
     *
     * @return array|null
     */
    public function getHeaderRightContent()
    {
        return $this->headerRightContent;
    }

    /**
     * Set the signoff content (EditorJS JSON)
     *
     * @param array|null $signoffContent
     *
     * @return MasterTemplate
     */
    public function setSignoffContent($signoffContent)
    {
        $this->signoffContent = $signoffContent;

        return $this;
    }

    /**
     * Get the signoff content (EditorJS JSON)
     *
     * @return array|null
     */
    public function getSignoffContent()
    {
        return $this->signoffContent;
    }

    /**
     * Set the footer content (EditorJS JSON)
     *
     * @param array|null $footerContent
     *
     * @return MasterTemplate
     */
    public function setFooterContent($footerContent)
    {
        $this->footerContent = $footerContent;

        return $this;
    }

    /**
     * Get the footer content (EditorJS JSON)
     *
     * @return array|null
     */
    public function getFooterContent()
    {
        return $this->footerContent;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return MasterTemplate
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

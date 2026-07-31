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
    #[ORM\Column(type: 'integer', name: 'id', nullable: false, options: ['unsigned' => true])]
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
     * Locale
     *
     * @var string
     */
    #[ORM\Column(type: 'string', name: 'locale', length: 20, nullable: true)]
    protected $locale;

    /**
     * Header left content
     *
     * @var array
     */
    #[ORM\Column(type: 'json', name: 'header_left_content', nullable: true)]
    protected $headerLeftContent;

    /**
     * Header right content
     *
     * @var array
     */
    #[ORM\Column(type: 'json', name: 'header_right_content', nullable: true)]
    protected $headerRightContent;

    /**
     * Signoff content
     *
     * @var array
     */
    #[ORM\Column(type: 'json', name: 'signoff_content', nullable: true)]
    protected $signoffContent;

    /**
     * Footer content
     *
     * @var array
     */
    #[ORM\Column(type: 'json', name: 'footer_content', nullable: true)]
    protected $footerContent;

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
     * Set the name
     *
     * @param string $name new value being set
     *
     * @return static
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
     * @return static
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
     * @return static
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
     * @return static
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
     * Set the header left content
     *
     * @param array $headerLeftContent new value being set
     *
     * @return static
     */
    public function setHeaderLeftContent($headerLeftContent)
    {
        $this->headerLeftContent = $headerLeftContent;

        return $this;
    }

    /**
     * Get the header left content
     *
     * @return array
     */
    public function getHeaderLeftContent()
    {
        return $this->headerLeftContent;
    }

    /**
     * Set the header right content
     *
     * @param array $headerRightContent new value being set
     *
     * @return static
     */
    public function setHeaderRightContent($headerRightContent)
    {
        $this->headerRightContent = $headerRightContent;

        return $this;
    }

    /**
     * Get the header right content
     *
     * @return array
     */
    public function getHeaderRightContent()
    {
        return $this->headerRightContent;
    }

    /**
     * Set the signoff content
     *
     * @param array $signoffContent new value being set
     *
     * @return static
     */
    public function setSignoffContent($signoffContent)
    {
        $this->signoffContent = $signoffContent;

        return $this;
    }

    /**
     * Get the signoff content
     *
     * @return array
     */
    public function getSignoffContent()
    {
        return $this->signoffContent;
    }

    /**
     * Set the footer content
     *
     * @param array $footerContent new value being set
     *
     * @return static
     */
    public function setFooterContent($footerContent)
    {
        $this->footerContent = $footerContent;

        return $this;
    }

    /**
     * Get the footer content
     *
     * @return array
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

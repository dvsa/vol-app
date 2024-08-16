<?php

namespace Dvsa\Olcs\Api\Entity\Doc;

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
 * DocTemplateBookmark Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="doc_template_bookmark",
 *    indexes={
 *        @ORM\Index(name="IDX_851FEE735653D501", columns={"doc_template_id"}),
 *        @ORM\Index(name="ix_doc_template_bookmark_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_doc_template_bookmark_doc_bookmark_id", columns={"doc_bookmark_id"}),
 *        @ORM\Index(name="ix_doc_template_bookmark_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_doc_template_bookmark_doc_template_id_doc_bookmark_id",
     *     columns={"doc_template_id","doc_bookmark_id"})
 *    }
 * )
 */
abstract class AbstractDocTemplateBookmark implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * Doc bookmark
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\DocBookmark
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\DocBookmark", fetch="LAZY")
     * @ORM\JoinColumn(name="doc_bookmark_id", referencedColumnName="id", nullable=false)
     */
    protected $docBookmark;

    /**
     * Doc template
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\DocTemplate
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Doc\DocTemplate",
     *     fetch="LAZY",
     *     inversedBy="docTemplateBookmarks"
     * )
     * @ORM\JoinColumn(name="doc_template_id", referencedColumnName="id", nullable=false)
     */
    protected $docTemplate;

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
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return DocTemplateBookmark
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
     * Set the doc bookmark
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\DocBookmark $docBookmark entity being set as the value
     *
     * @return DocTemplateBookmark
     */
    public function setDocBookmark($docBookmark)
    {
        $this->docBookmark = $docBookmark;

        return $this;
    }

    /**
     * Get the doc bookmark
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\DocBookmark
     */
    public function getDocBookmark()
    {
        return $this->docBookmark;
    }

    /**
     * Set the doc template
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\DocTemplate $docTemplate entity being set as the value
     *
     * @return DocTemplateBookmark
     */
    public function setDocTemplate($docTemplate)
    {
        $this->docTemplate = $docTemplate;

        return $this;
    }

    /**
     * Get the doc template
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\DocTemplate
     */
    public function getDocTemplate()
    {
        return $this->docTemplate;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return DocTemplateBookmark
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
     * @return DocTemplateBookmark
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
     * @return DocTemplateBookmark
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

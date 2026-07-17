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
 * AbstractLetterTypeIssue Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 */
#[ORM\Table(name: 'letter_type_issue')]
#[ORM\Index(name: 'ix_letter_type_issue_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_letter_type_issue_last_modified_by', columns: ['last_modified_by'])]
#[ORM\Index(name: 'ix_letter_type_issue_letter_issue_version_id', columns: ['letter_issue_version_id'])]
#[ORM\Index(name: 'IDX_DC298E9930450394', columns: ['letter_type_id'])]
#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractLetterTypeIssue implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * LetterType
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterType
     */
    #[ORM\Id]
    #[ORM\JoinColumn(name: 'letter_type_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Letter\LetterType::class, fetch: 'LAZY')]
    protected $letterType;

    /**
     * LetterIssueVersion
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion
     */
    #[ORM\Id]
    #[ORM\JoinColumn(name: 'letter_issue_version_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion::class, fetch: 'LAZY')]
    protected $letterIssueVersion;

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
     * Set the letter type
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterType $letterType new value being set
     *
     * @return static
     */
    public function setLetterType($letterType)
    {
        $this->letterType = $letterType;

        return $this;
    }

    /**
     * Get the letter type
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterType
     */
    public function getLetterType()
    {
        return $this->letterType;
    }

    /**
     * Set the letter issue version
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion $letterIssueVersion new value being set
     *
     * @return static
     */
    public function setLetterIssueVersion($letterIssueVersion)
    {
        $this->letterIssueVersion = $letterIssueVersion;

        return $this;
    }

    /**
     * Get the letter issue version
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion
     */
    public function getLetterIssueVersion()
    {
        return $this->letterIssueVersion;
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
     * Get bundle data
     */
    #[\Override]
    public function __toString(): string
    {
        return implode('-', [(string) $this->letterType, (string) $this->letterIssueVersion]);
    }
}

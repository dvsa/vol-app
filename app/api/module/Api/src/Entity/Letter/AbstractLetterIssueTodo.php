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
 * AbstractLetterIssueTodo Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_issue_todo",
 *    indexes={
 *        @ORM\Index(name="ix_letter_issue_todo_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_issue_todo_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_letter_issue_todo_letter_todo_version_id", columns={"letter_todo_version_id"}),
 *        @ORM\Index(name="IDX_C261543CE3496B9F", columns={"letter_issue_version_id"})
 *    }
 * )
 */
abstract class AbstractLetterIssueTodo implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Primary key
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="letter_issue_version_id", nullable=false)
     */
    protected $letter_issue_version_id = 0;

    /**
     * Primary key
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="letter_todo_version_id", nullable=false)
     */
    protected $letter_todo_version_id = 0;

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
     * Display order
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="display_order", nullable=false)
     */
    protected $displayOrder = 0;

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
     * Set the letter_issue_version_id
     *
     * @param int $letter_issue_version_id new value being set
     *
     * @return LetterIssueTodo
     */
    public function setLetter_issue_version_id($letter_issue_version_id)
    {
        $this->letter_issue_version_id = $letter_issue_version_id;

        return $this;
    }

    /**
     * Get the letter_issue_version_id
     *
     * @return int     */
    public function getLetter_issue_version_id()
    {
        return $this->letter_issue_version_id;
    }

    /**
     * Set the letter_todo_version_id
     *
     * @param int $letter_todo_version_id new value being set
     *
     * @return LetterIssueTodo
     */
    public function setLetter_todo_version_id($letter_todo_version_id)
    {
        $this->letter_todo_version_id = $letter_todo_version_id;

        return $this;
    }

    /**
     * Get the letter_todo_version_id
     *
     * @return int     */
    public function getLetter_todo_version_id()
    {
        return $this->letter_todo_version_id;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LetterIssueTodo
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return LetterIssueTodo
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the display order
     *
     * @param int $displayOrder new value being set
     *
     * @return LetterIssueTodo
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    /**
     * Get the display order
     *
     * @return int     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}

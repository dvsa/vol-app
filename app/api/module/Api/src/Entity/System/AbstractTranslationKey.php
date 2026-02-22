<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\System;

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
 * AbstractTranslationKey Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="translation_key",
 *    indexes={
 *        @ORM\Index(name="fk_translation_key_users_created_by", columns={"created_by"}),
 *        @ORM\Index(name="fk_translation_key_users_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="translation_key_translation_key_uindex", columns={"translation_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="translation_key_translation_key_uindex", columns={"translation_key"})
 *    }
 * )
 */
abstract class AbstractTranslationKey implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

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
     * Translation key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="translation_key", length=512, nullable=false)
     */
    protected $translationKey = '';

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=512, nullable=true)
     */
    protected $description;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=true, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * TranslationKeyTexts
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\System\TranslationKeyText", mappedBy="translationKey")
     */
    protected $translationKeyTexts;

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
        $this->translationKeyTexts = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return TranslationKey
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return TranslationKey
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
     * @return TranslationKey
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
     * Set the translation key
     *
     * @param string $translationKey new value being set
     *
     * @return TranslationKey
     */
    public function setTranslationKey($translationKey)
    {
        $this->translationKey = $translationKey;

        return $this;
    }

    /**
     * Get the translation key
     *
     * @return string     */
    public function getTranslationKey()
    {
        return $this->translationKey;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return TranslationKey
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return TranslationKey
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the translation key texts
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $translationKeyTexts collection being set as the value
     *
     * @return TranslationKey
     */
    public function setTranslationKeyTexts($translationKeyTexts)
    {
        $this->translationKeyTexts = $translationKeyTexts;

        return $this;
    }

    /**
     * Get the translation key texts
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslationKeyTexts()
    {
        return $this->translationKeyTexts;
    }

    /**
     * Add a translation key texts
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $translationKeyTexts collection being added
     *
     * @return TranslationKey
     */
    public function addTranslationKeyTexts($translationKeyTexts)
    {
        if ($translationKeyTexts instanceof ArrayCollection) {
            $this->translationKeyTexts = new ArrayCollection(
                array_merge(
                    $this->translationKeyTexts->toArray(),
                    $translationKeyTexts->toArray()
                )
            );
        } elseif (!$this->translationKeyTexts->contains($translationKeyTexts)) {
            $this->translationKeyTexts->add($translationKeyTexts);
        }

        return $this;
    }

    /**
     * Remove a translation key texts
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $translationKeyTexts collection being removed
     *
     * @return TranslationKey
     */
    public function removeTranslationKeyTexts($translationKeyTexts)
    {
        if ($this->translationKeyTexts->contains($translationKeyTexts)) {
            $this->translationKeyTexts->removeElement($translationKeyTexts);
        }

        return $this;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LetterSectionVariant Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_section_variant")
 */
class LetterSectionVariant extends AbstractLetterSectionVariant
{
    /**
     * Letter section versions
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion",
     *     mappedBy="letterSectionVariant",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"versionNumber" = "DESC"})
     */
    protected $versions;

    /**
     * Initialise collections
     */
    public function __construct()
    {
        $this->versions = new ArrayCollection();
    }

    /**
     * Get all versions
     *
     * @return ArrayCollection
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * Add a version
     *
     * @param LetterSectionVersion $version
     * @return self
     */
    public function addVersion(LetterSectionVersion $version)
    {
        if (!$this->versions->contains($version)) {
            $version->setLetterSectionVariant($this);
            $this->versions->add($version);
        }
        return $this;
    }

    /**
     * Remove a version
     *
     * @param LetterSectionVersion $version
     * @return self
     */
    public function removeVersion(LetterSectionVersion $version)
    {
        $this->versions->removeElement($version);
        return $this;
    }

    /**
     * Create a new version based on current version
     *
     * @return LetterSectionVersion
     */
    public function createNewVersion()
    {
        $currentVersion = $this->getCurrentVersion();
        if (!$currentVersion) {
            throw new \RuntimeException('No current version to base new version on');
        }

        $newVersion = new LetterSectionVersion();
        $newVersion->setLetterSectionVariant($this);
        $newVersion->setName($currentVersion->getName());
        $newVersion->setSectionType($currentVersion->getSectionType());
        $newVersion->setDefaultContent($currentVersion->getDefaultContent());
        $newVersion->setHelpText($currentVersion->getHelpText());
        $newVersion->setMinLength($currentVersion->getMinLength());
        $newVersion->setMaxLength($currentVersion->getMaxLength());
        $newVersion->setIsLocked(false);
        $newVersion->setRequiresInput($currentVersion->getRequiresInput());
        $newVersion->setIsNi($currentVersion->getIsNi());
        $newVersion->setGoodsOrPsv($currentVersion->getGoodsOrPsv());
        $newVersion->setVersionNumber($currentVersion->getVersionNumber() + 1);

        $this->addVersion($newVersion);

        return $newVersion;
    }

    /**
     * Check if this variant matches a given context
     *
     * NULL condition fields mean "matches any value" (wildcard).
     * Non-null fields must match the context exactly.
     *
     * @param array $context
     * @return bool
     */
    public function matchesContext(array $context): bool
    {
        if ($this->goodsOrPsv !== null && $this->goodsOrPsv->getId() !== ($context['goodsOrPsv'] ?? null)) {
            return false;
        }
        if ($this->isVariation !== null && $this->isVariation !== ($context['isVariation'] ?? null)) {
            return false;
        }
        if ($this->isNi !== null && $this->isNi !== ($context['isNi'] ?? null)) {
            return false;
        }
        if ($this->organisationType !== null && $this->organisationType->getId() !== ($context['organisationType'] ?? null)) {
            return false;
        }
        if ($this->letterChoice !== null && !in_array($this->letterChoice->getId(), $context['selectedChoiceIds'] ?? [])) {
            return false;
        }
        return true;
    }

    /**
     * Check if this is the default variant (all condition fields are null)
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->goodsOrPsv === null
            && $this->isVariation === null
            && $this->isNi === null
            && $this->organisationType === null
            && $this->letterChoice === null;
    }

    /**
     * Set a specific version as current
     *
     * @param LetterSectionVersion $version
     * @return self
     */
    public function setVersionAsCurrent(LetterSectionVersion $version)
    {
        if (!$this->versions->contains($version)) {
            throw new \InvalidArgumentException('Version does not belong to this variant');
        }

        $this->setCurrentVersion($version);
        return $this;
    }

    /**
     * Get the latest version (may not be current)
     *
     * @return LetterSectionVersion|null
     */
    public function getLatestVersion(): ?LetterSectionVersion
    {
        if ($this->versions->isEmpty()) {
            return null;
        }

        return $this->versions->first();
    }
}

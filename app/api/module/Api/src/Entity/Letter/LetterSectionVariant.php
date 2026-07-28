<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LetterSectionVariant Entity
 */
#[ORM\Table(name: 'letter_section_variant')]
#[ORM\Entity]
class LetterSectionVariant extends AbstractLetterSectionVariant
{
    /**
     * Letter section versions
     *
     * @var \Doctrine\Common\Collections\Collection<int, LetterSectionVersion>
     */
    #[ORM\OneToMany(targetEntity: \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion::class, mappedBy: 'letterSectionVariant', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['versionNumber' => 'DESC'])]
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
        return $this->explainMatch($context) === [];
    }

    /**
     * Which conditions stopped this variant matching.
     *
     * Same rules as matchesContext(), which defers to this so the two cannot drift. Reporting the
     * failing dimensions rather than a bare false is what lets an admin be told "rejected on
     * isVariation" instead of being left to work out why their wording never appears.
     *
     * @param array $context
     * @return string[] Failing dimension names, empty when the variant matches
     */
    public function explainMatch(array $context): array
    {
        $failed = [];

        if ($this->goodsOrPsv !== null && $this->goodsOrPsv->getId() !== ($context['goodsOrPsv'] ?? null)) {
            $failed[] = 'goodsOrPsv';
        }
        if ($this->isVariation !== null && $this->isVariation !== ($context['isVariation'] ?? null)) {
            $failed[] = 'isVariation';
        }
        if ($this->isNi !== null && $this->isNi !== ($context['isNi'] ?? null)) {
            $failed[] = 'isNi';
        }
        if ($this->organisationType !== null && $this->organisationType->getId() !== ($context['organisationType'] ?? null)) {
            $failed[] = 'organisationType';
        }
        if ($this->letterChoice !== null && !in_array($this->letterChoice->getId(), $context['selectedChoiceIds'] ?? [])) {
            $failed[] = 'letterChoice';
        }

        return $failed;
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
     * How many conditions this variant pins down.
     *
     * Used to pick the narrowest matching variant when several match the same context.
     *
     * @return int 0 for the default variant, up to 5 for a fully conditioned one
     */
    public function getSpecificity(): int
    {
        return (int) ($this->goodsOrPsv !== null)
            + (int) ($this->isVariation !== null)
            + (int) ($this->isNi !== null)
            + (int) ($this->organisationType !== null)
            + (int) ($this->letterChoice !== null);
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

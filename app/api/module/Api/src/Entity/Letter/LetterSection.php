<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LetterSection Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_section")
 */
class LetterSection extends AbstractLetterSection
{
    /**
     * Letter section versions
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion",
     *     mappedBy="letterSection",
     *     cascade={"persist"},
     *     orphanRemoval=false
     * )
     * @ORM\OrderBy({"versionNumber" = "DESC"})
     */
    protected $versions;

    /**
     * Non-persisted working properties for versioned fields
     * These hold changes until save
     */
    private $name;
    private $defaultContent;
    private $helpText;
    private $requiresInput;
    private $minLength;
    private $maxLength;
    private $sectionType;
    private $goodsOrPsv;
    private $isNi;

    /**
     * Initialise collections
     */
    public function __construct()
    {
        $this->versions = new ArrayCollection();
    }

    /**
     * Proxy getter for name
     *
     * @return string|null
     */
    public function getName()
    {
        if ($this->name !== null) {
            return $this->name;
        }
        return $this->currentVersion ? $this->currentVersion->getName() : null;
    }

    /**
     * Proxy setter for name
     *
     * @param string|null $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Proxy getter for defaultContent
     *
     * @return array|null
     */
    public function getDefaultContent()
    {
        if ($this->defaultContent !== null) {
            return $this->defaultContent;
        }
        return $this->currentVersion ? $this->currentVersion->getDefaultContent() : null;
    }

    /**
     * Proxy setter for defaultContent
     *
     * @param array|null $defaultContent
     * @return self
     */
    public function setDefaultContent($defaultContent)
    {
        $this->defaultContent = $defaultContent;
        return $this;
    }

    /**
     * Proxy getter for helpText
     *
     * @return string|null
     */
    public function getHelpText()
    {
        if ($this->helpText !== null) {
            return $this->helpText;
        }
        return $this->currentVersion ? $this->currentVersion->getHelpText() : null;
    }

    /**
     * Proxy setter for helpText
     *
     * @param string|null $helpText
     * @return self
     */
    public function setHelpText($helpText)
    {
        $this->helpText = $helpText;
        return $this;
    }

    /**
     * Proxy getter for requiresInput
     *
     * @return bool
     */
    public function getRequiresInput()
    {
        if ($this->requiresInput !== null) {
            return $this->requiresInput;
        }
        return $this->currentVersion ? $this->currentVersion->getRequiresInput() : false;
    }

    /**
     * Proxy setter for requiresInput
     *
     * @param bool $requiresInput
     * @return self
     */
    public function setRequiresInput($requiresInput)
    {
        $this->requiresInput = $requiresInput;
        return $this;
    }

    /**
     * Proxy getter for minLength
     *
     * @return int|null
     */
    public function getMinLength()
    {
        if ($this->minLength !== null) {
            return $this->minLength;
        }
        return $this->currentVersion ? $this->currentVersion->getMinLength() : null;
    }

    /**
     * Proxy setter for minLength
     *
     * @param int|null $minLength
     * @return self
     */
    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;
        return $this;
    }

    /**
     * Proxy getter for maxLength
     *
     * @return int|null
     */
    public function getMaxLength()
    {
        if ($this->maxLength !== null) {
            return $this->maxLength;
        }
        return $this->currentVersion ? $this->currentVersion->getMaxLength() : null;
    }

    /**
     * Proxy setter for maxLength
     *
     * @param int|null $maxLength
     * @return self
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * Proxy getter for sectionType
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData|null
     */
    public function getSectionType()
    {
        if ($this->sectionType !== null) {
            return $this->sectionType;
        }
        return $this->currentVersion ? $this->currentVersion->getSectionType() : null;
    }

    /**
     * Proxy setter for sectionType
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData|null $sectionType
     * @return self
     */
    public function setSectionType($sectionType)
    {
        $this->sectionType = $sectionType;
        return $this;
    }

    /**
     * Proxy getter for goodsOrPsv
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData|null
     */
    public function getGoodsOrPsv()
    {
        if ($this->goodsOrPsv !== null) {
            return $this->goodsOrPsv;
        }
        return $this->currentVersion ? $this->currentVersion->getGoodsOrPsv() : null;
    }

    /**
     * Proxy setter for goodsOrPsv
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData|null $goodsOrPsv
     * @return self
     */
    public function setGoodsOrPsv($goodsOrPsv)
    {
        $this->goodsOrPsv = $goodsOrPsv;
        return $this;
    }

    /**
     * Proxy getter for isNi
     *
     * @return bool
     */
    public function getIsNi()
    {
        if ($this->isNi !== null) {
            return $this->isNi;
        }
        return $this->currentVersion ? $this->currentVersion->getIsNi() : false;
    }

    /**
     * Proxy setter for isNi
     *
     * @param bool $isNi
     * @return self
     */
    public function setIsNi($isNi)
    {
        $this->isNi = $isNi;
        return $this;
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
            $version->setLetterSection($this);
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
        $newVersion->setLetterSection($this);
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
     * Get the latest version (may not be current)
     *
     * @return LetterSectionVersion|null
     */
    public function getLatestVersion()
    {
        if ($this->versions->isEmpty()) {
            return null;
        }

        return $this->versions->first();
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
            throw new \InvalidArgumentException('Version does not belong to this section');
        }

        $this->setCurrentVersion($version);
        return $this;
    }
}

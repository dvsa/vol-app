<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LetterIssue Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_issue")
 */
class LetterIssue extends AbstractLetterIssue
{
    /**
     * Letter issue versions
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion",
     *     mappedBy="letterIssue",
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
    private $category;
    private $subCategory;
    private $heading;
    private $defaultBodyContent;
    private $helpText;
    private $minLength;
    private $maxLength;
    private $requiresInput;
    private $isNi;
    private $goodsOrPsv;

    /**
     * Initialise collections
     */
    public function __construct()
    {
        $this->versions = new ArrayCollection();
    }

    /**
     * Proxy getter for category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData|null
     */
    public function getCategory()
    {
        if ($this->category !== null) {
            return $this->category;
        }
        return $this->currentVersion ? $this->currentVersion->getCategory() : null;
    }

    /**
     * Proxy setter for category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData|null $category
     * @return self
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Proxy getter for subCategory
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData|null
     */
    public function getSubCategory()
    {
        if ($this->subCategory !== null) {
            return $this->subCategory;
        }
        return $this->currentVersion ? $this->currentVersion->getSubCategory() : null;
    }

    /**
     * Proxy setter for subCategory
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData|null $subCategory
     * @return self
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;
        return $this;
    }

    /**
     * Proxy getter for heading
     *
     * @return string|null
     */
    public function getHeading()
    {
        if ($this->heading !== null) {
            return $this->heading;
        }
        return $this->currentVersion ? $this->currentVersion->getHeading() : null;
    }

    /**
     * Proxy setter for heading
     *
     * @param string|null $heading
     * @return self
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;
        return $this;
    }

    /**
     * Proxy getter for defaultBodyContent
     *
     * @return array|null
     */
    public function getDefaultBodyContent()
    {
        if ($this->defaultBodyContent !== null) {
            return $this->defaultBodyContent;
        }
        return $this->currentVersion ? $this->currentVersion->getDefaultBodyContent() : null;
    }

    /**
     * Proxy setter for defaultBodyContent
     *
     * @param array|null $defaultBodyContent
     * @return self
     */
    public function setDefaultBodyContent($defaultBodyContent)
    {
        $this->defaultBodyContent = $defaultBodyContent;
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
     * @param LetterIssueVersion $version
     * @return self
     */
    public function addVersion(LetterIssueVersion $version)
    {
        if (!$this->versions->contains($version)) {
            $version->setLetterIssue($this);
            $this->versions->add($version);
        }
        return $this;
    }

    /**
     * Remove a version
     *
     * @param LetterIssueVersion $version
     * @return self
     */
    public function removeVersion(LetterIssueVersion $version)
    {
        $this->versions->removeElement($version);
        return $this;
    }

    /**
     * Create a new version based on current version
     *
     * @return LetterIssueVersion
     */
    public function createNewVersion()
    {
        $currentVersion = $this->getCurrentVersion();
        if (!$currentVersion) {
            throw new \RuntimeException('No current version to base new version on');
        }

        $newVersion = new LetterIssueVersion();
        $newVersion->setLetterIssue($this);
        $newVersion->setCategory($currentVersion->getCategory());
        $newVersion->setSubCategory($currentVersion->getSubCategory());
        $newVersion->setHeading($currentVersion->getHeading());
        $newVersion->setDefaultBodyContent($currentVersion->getDefaultBodyContent());
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
     * @return LetterIssueVersion|null
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
     * @param LetterIssueVersion $version
     * @return self
     */
    public function setVersionAsCurrent(LetterIssueVersion $version)
    {
        if (!$this->versions->contains($version)) {
            throw new \InvalidArgumentException('Version does not belong to this issue');
        }

        $this->setCurrentVersion($version);
        return $this;
    }

    /**
     * Get versions for a specific category
     *
     * @param int $categoryId
     * @return ArrayCollection
     */
    public function getVersionsByCategory($categoryId)
    {
        return $this->versions->filter(function (LetterIssueVersion $version) use ($categoryId) {
            return $version->getCategory() && $version->getCategory()->getId() === $categoryId;
        });
    }

    /**
     * Get published versions only
     *
     * @return ArrayCollection
     */
    public function getPublishedVersions()
    {
        return $this->versions->filter(function (LetterIssueVersion $version) {
            return $version->isPublished();
        });
    }
}

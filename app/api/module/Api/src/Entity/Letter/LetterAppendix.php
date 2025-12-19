<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LetterAppendix Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_appendix")
 */
class LetterAppendix extends AbstractLetterAppendix
{
    /**
     * Letter appendix versions
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion",
     *     mappedBy="letterAppendix",
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
    private $description;
    private $document;

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
     * Proxy getter for description
     *
     * @return string|null
     */
    public function getDescription()
    {
        if ($this->description !== null) {
            return $this->description;
        }
        return $this->currentVersion ? $this->currentVersion->getDescription() : null;
    }

    /**
     * Proxy setter for description
     *
     * @param string|null $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Proxy getter for document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document|null
     */
    public function getDocument()
    {
        if ($this->document !== null) {
            return $this->document;
        }
        return $this->currentVersion ? $this->currentVersion->getDocument() : null;
    }

    /**
     * Proxy setter for document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document|null $document
     * @return self
     */
    public function setDocument($document)
    {
        $this->document = $document;
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
     * @param LetterAppendixVersion $version
     * @return self
     */
    public function addVersion(LetterAppendixVersion $version)
    {
        if (!$this->versions->contains($version)) {
            $version->setLetterAppendix($this);
            $this->versions->add($version);
        }
        return $this;
    }

    /**
     * Remove a version
     *
     * @param LetterAppendixVersion $version
     * @return self
     */
    public function removeVersion(LetterAppendixVersion $version)
    {
        $this->versions->removeElement($version);
        return $this;
    }

    /**
     * Create a new version based on current version
     *
     * @return LetterAppendixVersion
     */
    public function createNewVersion()
    {
        $currentVersion = $this->getCurrentVersion();
        if (!$currentVersion) {
            throw new \RuntimeException('No current version to base new version on');
        }

        $newVersion = new LetterAppendixVersion();
        $newVersion->setLetterAppendix($this);
        $newVersion->setName($currentVersion->getName());
        $newVersion->setDescription($currentVersion->getDescription());
        $newVersion->setDocument($currentVersion->getDocument());
        $newVersion->setIsLocked(false);
        $newVersion->setVersionNumber($currentVersion->getVersionNumber() + 1);

        $this->addVersion($newVersion);

        return $newVersion;
    }

    /**
     * Get the latest version (may not be current)
     *
     * @return LetterAppendixVersion|null
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
     * @param LetterAppendixVersion $version
     * @return self
     */
    public function setVersionAsCurrent(LetterAppendixVersion $version)
    {
        if (!$this->versions->contains($version)) {
            throw new \InvalidArgumentException('Version does not belong to this appendix');
        }

        $this->setCurrentVersion($version);
        return $this;
    }

    /**
     * Get published versions only
     *
     * @return ArrayCollection
     */
    public function getPublishedVersions()
    {
        return $this->versions->filter(function (LetterAppendixVersion $version) {
            return $version->isPublished();
        });
    }

    /**
     * Get versions with documents
     *
     * @return ArrayCollection
     */
    public function getVersionsWithDocuments()
    {
        return $this->versions->filter(function (LetterAppendixVersion $version) {
            return $version->getDocument() !== null;
        });
    }
}

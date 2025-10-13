<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LetterTodo Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_todo")
 */
class LetterTodo extends AbstractLetterTodo
{
    /**
     * Letter todo versions
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion",
     *     mappedBy="letterTodo",
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
    private $description;
    private $helpText;

    /**
     * Initialise collections
     */
    public function __construct()
    {
        $this->versions = new ArrayCollection();
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
     * @param LetterTodoVersion $version
     * @return self
     */
    public function addVersion(LetterTodoVersion $version)
    {
        if (!$this->versions->contains($version)) {
            $version->setLetterTodo($this);
            $this->versions->add($version);
        }
        return $this;
    }

    /**
     * Remove a version
     *
     * @param LetterTodoVersion $version
     * @return self
     */
    public function removeVersion(LetterTodoVersion $version)
    {
        $this->versions->removeElement($version);
        return $this;
    }

    /**
     * Create a new version based on current version
     *
     * @return LetterTodoVersion
     */
    public function createNewVersion()
    {
        $currentVersion = $this->getCurrentVersion();
        if (!$currentVersion) {
            throw new \RuntimeException('No current version to base new version on');
        }

        $newVersion = new LetterTodoVersion();
        $newVersion->setLetterTodo($this);
        $newVersion->setDescription($currentVersion->getDescription());
        $newVersion->setHelpText($currentVersion->getHelpText());
        $newVersion->setIsLocked(false);
        $newVersion->setVersionNumber($currentVersion->getVersionNumber() + 1);

        $this->addVersion($newVersion);

        return $newVersion;
    }

    /**
     * Get the latest version (may not be current)
     *
     * @return LetterTodoVersion|null
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
     * @param LetterTodoVersion $version
     * @return self
     */
    public function setVersionAsCurrent(LetterTodoVersion $version)
    {
        if (!$this->versions->contains($version)) {
            throw new \InvalidArgumentException('Version does not belong to this todo');
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
        return $this->versions->filter(function (LetterTodoVersion $version) {
            return $version->isPublished();
        });
    }
}

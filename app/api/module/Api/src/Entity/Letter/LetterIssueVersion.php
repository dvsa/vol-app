<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LetterIssueVersion Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_issue_version")
 */
class LetterIssueVersion extends AbstractLetterIssueVersion
{
    /**
     * Letter issue todos
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterIssueTodo",
     *     mappedBy="letterIssueVersion",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     */
    protected $letterIssueTodos;

    /**
     * Initialise collections
     */
    public function __construct()
    {
        $this->letterIssueTodos = new ArrayCollection();
    }

    /**
     * Get letter issue todos
     *
     * @return ArrayCollection
     */
    public function getLetterIssueTodos()
    {
        return $this->letterIssueTodos;
    }

    /**
     * Add letter issue todo
     *
     * @param LetterIssueTodo $letterIssueTodo
     * @return self
     */
    public function addLetterIssueTodo(LetterIssueTodo $letterIssueTodo)
    {
        if (!$this->letterIssueTodos->contains($letterIssueTodo)) {
            $letterIssueTodo->setLetterIssueVersion($this);
            $this->letterIssueTodos->add($letterIssueTodo);
        }
        return $this;
    }

    /**
     * Remove letter issue todo
     *
     * @param LetterIssueTodo $letterIssueTodo
     * @return self
     */
    public function removeLetterIssueTodo(LetterIssueTodo $letterIssueTodo)
    {
        $this->letterIssueTodos->removeElement($letterIssueTodo);
        return $this;
    }

    /**
     * Check if this version is published
     *
     * @return bool
     */
    public function isPublished()
    {
        if ($this->publishFrom === null) {
            return true;
        }

        return $this->publishFrom <= new \DateTime();
    }

    /**
     * Check if this version is embargoed
     *
     * @return bool
     */
    public function isEmbargoed()
    {
        return !$this->isPublished();
    }

    /**
     * Check if this version is the current version
     *
     * @return bool
     */
    public function isCurrent()
    {
        $issue = $this->getLetterIssue();
        if (!$issue) {
            return false;
        }

        return $issue->getCurrentVersion() === $this;
    }

    /**
     * Lock this version
     *
     * @return self
     */
    public function lock()
    {
        $this->isLocked = true;
        return $this;
    }

    /**
     * Unlock this version
     *
     * @return self
     */
    public function unlock()
    {
        $this->isLocked = false;
        return $this;
    }

    /**
     * Check if content is valid based on length constraints
     *
     * @param string $content
     * @return bool
     */
    public function isContentValid($content)
    {
        $length = strlen($content);

        if ($this->minLength !== null && $length < $this->minLength) {
            return false;
        }

        if ($this->maxLength !== null && $length > $this->maxLength) {
            return false;
        }

        return true;
    }

    /**
     * Get default body content as array (for EditorJS)
     *
     * @return array
     */
    public function getDefaultBodyContentAsArray()
    {
        return $this->defaultBodyContent ?: [];
    }

    /**
     * Set default body content from array (for EditorJS)
     *
     * @param array $content
     * @return self
     */
    public function setDefaultBodyContentFromArray(array $content)
    {
        $this->defaultBodyContent = $content;
        return $this;
    }

    /**
     * Check if this version applies to NI
     *
     * @return bool
     */
    public function appliesToNi()
    {
        return $this->isNi;
    }

    /**
     * Check if this version applies to a specific goods/PSV type
     *
     * @param string $type
     * @return bool
     */
    public function appliesToType($type)
    {
        if ($this->goodsOrPsv === null) {
            return true;
        }

        return $this->goodsOrPsv->getId() === $type;
    }

    /**
     * Check if this version matches category and subcategory
     *
     * @param int $categoryId
     * @param int|null $subCategoryId
     * @return bool
     */
    public function matchesCategory($categoryId, $subCategoryId = null)
    {
        if ($this->category->getId() !== $categoryId) {
            return false;
        }

        if ($subCategoryId !== null && $this->subCategory) {
            return $this->subCategory->getId() === $subCategoryId;
        }

        return true;
    }
}

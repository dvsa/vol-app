<?php

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LetterInstanceIssue Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_instance_issue",
 *    indexes={
 *        @ORM\Index(name="ix_letter_instance_issue_letter_instance_id", columns={"letter_instance_id"}),
 *        @ORM\Index(name="ix_letter_instance_issue_letter_issue_version_id", columns={"letter_issue_version_id"}),
 *        @ORM\Index(name="ix_letter_instance_issue_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_instance_issue_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class LetterInstanceIssue extends AbstractLetterInstanceIssue
{
    /**
     * Letter instance todos
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo",
     *     mappedBy="letterInstanceIssue",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     */
    protected $letterInstanceTodos;

    /**
     * Initialise collections
     */
    public function __construct()
    {
        $this->letterInstanceTodos = new ArrayCollection();
    }

    /**
     * Get letter instance todos
     *
     * @return ArrayCollection
     */
    public function getLetterInstanceTodos()
    {
        return $this->letterInstanceTodos;
    }

    /**
     * Get the effective content (edited or default)
     *
     * @return array
     */
    public function getEffectiveContent()
    {
        if (!empty($this->editedContent)) {
            return $this->editedContent;
        }
        
        return $this->letterIssueVersion->getDefaultBodyContent() ?: [];
    }

    /**
     * Check if content has been edited
     *
     * @return bool
     */
    public function hasBeenEdited()
    {
        return !empty($this->editedContent);
    }

    /**
     * Clear edited content
     *
     * @return self
     */
    public function clearEditedContent()
    {
        $this->editedContent = null;
        return $this;
    }

    /**
     * Set edited content from array
     *
     * @param array $content
     * @return self
     */
    public function setEditedContentFromArray(array $content)
    {
        $this->editedContent = $content;
        return $this;
    }

    /**
     * Check if this issue requires input
     *
     * @return bool
     */
    public function requiresInput()
    {
        return $this->letterIssueVersion->getRequiresInput();
    }

    /**
     * Get issue heading
     *
     * @return string
     */
    public function getHeading()
    {
        return $this->letterIssueVersion->getHeading();
    }

    /**
     * Check if content is valid
     *
     * @return bool
     */
    public function isContentValid()
    {
        if ($this->requiresInput() && !$this->hasBeenEdited()) {
            return false;
        }
        
        $content = $this->getEffectiveContent();
        $contentString = json_encode($content);
        
        return $this->letterIssueVersion->isContentValid($contentString);
    }
}
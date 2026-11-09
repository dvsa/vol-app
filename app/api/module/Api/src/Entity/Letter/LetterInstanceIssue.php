<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Service\Letter\EditorJsText;

/**
 * LetterInstanceIssue Entity
 */
#[ORM\Table(name: 'letter_instance_issue')]
#[ORM\Entity]
class LetterInstanceIssue extends AbstractLetterInstanceIssue
{
    /**
     * Letter instance todos
     *
     * @var \Doctrine\Common\Collections\Collection<int, LetterInstanceTodo>
     */
    #[ORM\OneToMany(targetEntity: \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo::class, mappedBy: 'letterInstanceIssue', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['displayOrder' => \SortDirection::Ascending])]
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
     * Add a letter instance todo, keeping both sides of the association in step
     */
    public function addLetterInstanceTodo(LetterInstanceTodo $todo): void
    {
        if (!$this->letterInstanceTodos->contains($todo)) {
            $todo->setLetterInstanceIssue($this);
            $this->letterInstanceTodos->add($todo);
        }
    }

    /**
     * The caseworker's own to-dos on this issue (an Other issue), in the order they were written
     *
     * @return list<LetterInstanceTodo>
     */
    public function getCustomTodos(): array
    {
        $custom = array_values(array_filter(
            $this->letterInstanceTodos->toArray(),
            static fn(LetterInstanceTodo $todo): bool => $todo->isCustom()
        ));
        usort($custom, static fn(LetterInstanceTodo $a, LetterInstanceTodo $b): int => $a->getDisplayOrder() <=> $b->getDisplayOrder());

        return $custom;
    }

    /**
     * Remove a letter instance todo from this issue's collection
     */
    public function removeLetterInstanceTodo(LetterInstanceTodo $todo): void
    {
        $this->letterInstanceTodos->removeElement($todo);
    }

    /**
     * Get the effective content (edited or default)
     *
     * Handles both properly stored arrays and double-encoded JSON strings
     *
     * @return array
     */
    public function getEffectiveContent()
    {
        if (!empty($this->editedContent)) {
            $content = $this->editedContent;

            // If content is a string, it was double-encoded - decode it
            if (is_string($content)) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
                return [];
            }

            return $content;
        }

        if (!empty($this->generatedContent) && is_array($this->generatedContent)) {
            return $this->generatedContent;
        }

        return $this->letterIssueVersion->getDefaultBodyContentAsArray() ?: [];
    }

    /**
     * Check if content has been edited
     *
     * @return bool
     */
    public function hasBeenEdited()
    {
        return !empty($this->editedContent) || ($this->isOther() && $this->hasWrittenHeading());
    }

    /**
     * An Other issue: the caseworker writes its heading and may add their own to-dos (VOL-7735)
     */
    public function isOther(): bool
    {
        return (bool) $this->letterIssueVersion?->isOther();
    }

    /**
     * Whether this issue still needs the caseworker before the letter can be sent.
     *
     * A standard issue needs input while it is flagged and unedited. An Other issue needs a
     * heading and a body that says something other than the placeholder: the edit page's Save
     * posts the editor even when nothing changed, so "edited" alone isn't enough (VOL-7735).
     */
    public function isInputPending(): bool
    {
        if (!$this->isOther()) {
            return $this->requiresInput() && !$this->hasBeenEdited();
        }

        if (!$this->hasWrittenHeading() || empty($this->editedContent)) {
            return true;
        }

        $body = EditorJsText::plain($this->getEffectiveContent());
        $placeholder = EditorJsText::plain($this->letterIssueVersion->getDefaultBodyContentAsArray());

        return $body === '' || $body === $placeholder;
    }

    /**
     * How the issue is named away from the letter (lists of what needs input, what will be lost).
     * An Other issue with no heading yet adds its type, so two of them can be told apart.
     */
    public function getDisplayName(): string
    {
        $heading = (string) $this->getHeading();
        if (!$this->isOther() || $this->hasWrittenHeading()) {
            return $heading;
        }

        // the general Other issue sits in its own "Other issues" type, which says nothing extra
        $type = $this->letterIssueVersion?->getLetterIssueType();
        if ($type === null || $type->getCode() === LetterIssueType::CODE_OTHER || !$type->getName()) {
            return $heading;
        }

        return $heading . ' (' . $type->getName() . ')';
    }

    private function hasWrittenHeading(): bool
    {
        return trim((string) $this->editedHeading) !== '';
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
        // an Other issue prints the caseworker's heading once they've written one (VOL-7735)
        if ($this->isOther() && $this->hasWrittenHeading()) {
            return trim((string) $this->editedHeading);
        }

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

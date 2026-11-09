<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LetterInstance Entity
 */
#[ORM\Table(name: 'letter_instance')]
#[ORM\Entity]
class LetterInstance extends AbstractLetterInstance
{
    public const STATUS_DRAFT = 'ltr_sts_draft';
    public const STATUS_READY = 'ltr_sts_ready';
    public const STATUS_SENT = 'ltr_sts_sent';
    public const STATUS_FAILED = 'ltr_sts_failed';
    public const STATUS_CANCELLED = 'ltr_sts_cancelled';

    /**
     * Where a to-do shared by several issues appears in the letter: after the first of those
     * issues, or after the last (VOL-7737). The stored owner stays the first issue either way
     * (VOL-7280/7408), so this only changes where it is drawn and can be flipped at any time.
     */
    public const SHARED_TODO_UNDER_FIRST_ISSUE = 'first';
    public const SHARED_TODO_UNDER_LAST_ISSUE = 'last';
    public const SHARED_TODO_PLACEMENT = self::SHARED_TODO_UNDER_FIRST_ISSUE;

    /**
     * Memoised issues that bring each to-do, in letter order, keyed by LetterTodoVersion::getDedupeKey.
     * Not mapped -- derived from the issues.
     *
     * @var array<string, list<LetterInstanceIssue>>|null
     */
    private ?array $todoBringingIssues = null;

    /**
     * Letter instance sections
     *
     * @var \Doctrine\Common\Collections\Collection<int, LetterInstanceSection>
     */
    #[ORM\OneToMany(targetEntity: \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection::class, mappedBy: 'letterInstance', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['displayOrder' => \SortDirection::Ascending])]
    protected $letterInstanceSections;

    /**
     * Letter instance issues
     *
     * @var \Doctrine\Common\Collections\Collection<int, LetterInstanceIssue>
     */
    #[ORM\OneToMany(targetEntity: \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue::class, mappedBy: 'letterInstance', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['displayOrder' => \SortDirection::Ascending])]
    protected $letterInstanceIssues;

    /**
     * Letter instance todos
     *
     * @var \Doctrine\Common\Collections\Collection<int, LetterInstanceTodo>
     */
    #[ORM\OneToMany(targetEntity: \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo::class, mappedBy: 'letterInstance', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['displayOrder' => \SortDirection::Ascending])]
    protected $letterInstanceTodos;

    /**
     * Letter instance appendices
     *
     * @var \Doctrine\Common\Collections\Collection<int, LetterInstanceAppendix>
     */
    #[ORM\OneToMany(targetEntity: \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceAppendix::class, mappedBy: 'letterInstance', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['displayOrder' => \SortDirection::Ascending])]
    protected $letterInstanceAppendices;

    /**
     * Letter instance choices (selected by caseworker)
     *
     * @var \Doctrine\Common\Collections\Collection<int, LetterInstanceChoice>
     */
    #[ORM\OneToMany(targetEntity: \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceChoice::class, mappedBy: 'letterInstance', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected $letterInstanceChoices;

    /**
     * Initialise collections
     */
    public function __construct()
    {
        $this->letterInstanceSections = new ArrayCollection();
        $this->letterInstanceIssues = new ArrayCollection();
        $this->letterInstanceTodos = new ArrayCollection();
        $this->letterInstanceAppendices = new ArrayCollection();
        $this->letterInstanceChoices = new ArrayCollection();
    }

    /**
     * Get letter instance sections
     *
     * @return ArrayCollection
     */
    public function getLetterInstanceSections()
    {
        return $this->letterInstanceSections;
    }

    /**
     * Add letter instance section
     *
     * @param LetterInstanceSection $section
     * @return self
     */
    public function addLetterInstanceSection(LetterInstanceSection $section)
    {
        if (!$this->letterInstanceSections->contains($section)) {
            $section->setLetterInstance($this);
            $this->letterInstanceSections->add($section);
        }
        return $this;
    }

    /**
     * Get letter instance issues
     *
     * @return ArrayCollection
     */
    public function getLetterInstanceIssues()
    {
        return $this->letterInstanceIssues;
    }

    /**
     * Add letter instance issue
     *
     * @param LetterInstanceIssue $issue
     * @return self
     */
    public function addLetterInstanceIssue(LetterInstanceIssue $issue)
    {
        if (!$this->letterInstanceIssues->contains($issue)) {
            $issue->setLetterInstance($this);
            $this->letterInstanceIssues->add($issue);
            $this->todoBringingIssues = null;
        }
        return $this;
    }

    /**
     * Remove letter instance issue
     */
    public function removeLetterInstanceIssue(LetterInstanceIssue $issue): void
    {
        $this->letterInstanceIssues->removeElement($issue);
        $this->todoBringingIssues = null;
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
     * Add letter instance todo
     *
     * @param LetterInstanceTodo $todo
     * @return self
     */
    public function addLetterInstanceTodo(LetterInstanceTodo $todo)
    {
        if (!$this->letterInstanceTodos->contains($todo)) {
            $todo->setLetterInstance($this);
            $this->letterInstanceTodos->add($todo);
        }
        return $this;
    }

    /**
     * Remove letter instance todo
     */
    public function removeLetterInstanceTodo(LetterInstanceTodo $todo): void
    {
        $this->letterInstanceTodos->removeElement($todo);
        $this->todoBringingIssues = null;
    }

    /**
     * How many of this letter's issues require each to-do, keyed by LetterTodoVersion::getDedupeKey
     * so that issues linking different versions of the same to-do count towards one total (VOL-7408).
     *
     * A to-do is deduplicated when it is composed, so it appears once no matter how many of the
     * selected issues call for it. A caseworker editing it therefore has no way to see that it is
     * standing in for several issues. This supplies that count.
     *
     * @return array<string, int>
     */
    public function getTodoRequiringIssueCounts(): array
    {
        return array_map(count(...), $this->todoBringingIssues());
    }

    /**
     * The ids of the issues each to-do can be shown under, preferred first, keyed by
     * LetterTodoVersion::getDedupeKey. The edit page uses the first one it is showing.
     *
     * @return array<string, list<int>>
     */
    public function getTodoPlacementIssueIds(string $placement = self::SHARED_TODO_PLACEMENT): array
    {
        return array_map(
            static fn(array $issues): array => array_map(
                static fn(LetterInstanceIssue $issue): int => (int) $issue->getId(),
                $issues
            ),
            $this->todoPlacementIssues($placement)
        );
    }

    /**
     * The issue a to-do is drawn after in the letter (VOL-7737). A to-do none of the letter's
     * issues brings any more stays with the issue it belongs to.
     */
    public function getPlacementIssue(
        LetterInstanceTodo $todo,
        string $placement = self::SHARED_TODO_PLACEMENT
    ): ?LetterInstanceIssue {
        $key = $todo->getLetterTodoVersion()?->getDedupeKey();
        $issues = $key !== null ? ($this->todoPlacementIssues($placement)[$key] ?? []) : [];

        return $issues[0] ?? $todo->getLetterInstanceIssue();
    }

    /**
     * @return array<string, list<LetterInstanceIssue>>
     */
    private function todoPlacementIssues(string $placement): array
    {
        $bringing = $this->todoBringingIssues();

        return $placement === self::SHARED_TODO_UNDER_LAST_ISSUE
            ? array_map(array_reverse(...), $bringing)
            : $bringing;
    }

    /**
     * The issues that bring each to-do, in letter order.
     *
     * Built in ONE pass over the issues and memoised, deliberately. Asking each to-do to work its
     * own answer out would walk every issue again per to-do, and letterIssueTodos is a LAZY
     * association, so that shape is an N+1. This way the lazy load happens once per issue for the
     * whole letter.
     *
     * @return array<string, list<LetterInstanceIssue>>
     */
    private function todoBringingIssues(): array
    {
        if ($this->todoBringingIssues !== null) {
            return $this->todoBringingIssues;
        }

        $issues = array_values($this->getLetterInstanceIssues()->toArray());
        usort(
            $issues,
            static fn(LetterInstanceIssue $a, LetterInstanceIssue $b): int => $a->getDisplayOrder() <=> $b->getDisplayOrder()
        );

        $bringing = [];
        foreach ($issues as $instanceIssue) {
            foreach ($instanceIssue->getLetterIssueVersion()?->getLetterIssueTodos() ?? [] as $issueTodo) {
                $todoVersion = $issueTodo->getLetterTodoVersion();
                if ($todoVersion === null) {
                    continue;
                }

                $key = $todoVersion->getDedupeKey();
                if (!in_array($instanceIssue, $bringing[$key] ?? [], true)) {
                    $bringing[$key][] = $instanceIssue;
                }
            }
        }

        $this->todoBringingIssues = $bringing;

        return $bringing;
    }

    /**
     * Get letter instance appendices
     *
     * @return ArrayCollection
     */
    public function getLetterInstanceAppendices()
    {
        return $this->letterInstanceAppendices;
    }

    /**
     * Add letter instance appendix
     *
     * @param LetterInstanceAppendix $appendix
     * @return self
     */
    public function addLetterInstanceAppendix(LetterInstanceAppendix $appendix)
    {
        if (!$this->letterInstanceAppendices->contains($appendix)) {
            $appendix->setLetterInstance($this);
            $this->letterInstanceAppendices->add($appendix);
        }
        return $this;
    }

    /**
     * Check if letter is draft
     *
     * @return bool
     */
    public function isDraft()
    {
        return $this->status && $this->status->getId() === self::STATUS_DRAFT;
    }

    /**
     * Check if letter is ready
     *
     * @return bool
     */
    public function isReady()
    {
        return $this->status && $this->status->getId() === self::STATUS_READY;
    }

    /**
     * Check if letter is sent
     *
     * @return bool
     */
    public function isSent()
    {
        return $this->status && $this->status->getId() === self::STATUS_SENT;
    }

    /**
     * Check if letter can be edited
     *
     * @return bool
     */
    public function canEdit()
    {
        return $this->isDraft() && !$this->isDeleted();
    }

    /**
     * Check if letter can be sent
     *
     * @return bool
     */
    public function canSend()
    {
        return $this->isReady() && !$this->isDeleted();
    }

    /**
     * Mark as sent
     *
     * @return self
     */
    public function markAsSent()
    {
        $this->sentOn = new \DateTime();
        return $this;
    }

    /**
     * Get the related entity (licence, application, case, etc.)
     *
     * @return mixed|null
     */
    public function getRelatedEntity()
    {
        if ($this->licence !== null) {
            return $this->licence;
        }
        if ($this->application !== null) {
            return $this->application;
        }
        if ($this->case !== null) {
            return $this->case;
        }
        if ($this->busReg !== null) {
            return $this->busReg;
        }
        if ($this->irfoOrganisation !== null) {
            return $this->irfoOrganisation;
        }
        return null;
    }

    /**
     * Get the recipient (organisation or transport manager)
     *
     * @return mixed|null
     */
    public function getRecipient()
    {
        if ($this->transportManager !== null) {
            return $this->transportManager;
        }
        if ($this->organisation !== null) {
            return $this->organisation;
        }
        return null;
    }

    /**
     * Check if all required sections have content
     *
     * @return bool
     */
    public function hasAllRequiredContent()
    {
        foreach ($this->letterInstanceSections as $section) {
            if (
                $section->getLetterSectionVersion()->getRequiresInput() &&
                empty($section->getEditedContent())
            ) {
                return false;
            }
        }

        foreach ($this->letterInstanceIssues as $issue) {
            if (
                $issue->getLetterIssueVersion()->getRequiresInput() &&
                empty($issue->getEditedContent())
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get letter instance choices
     *
     * @return ArrayCollection
     */
    public function getLetterInstanceChoices()
    {
        return $this->letterInstanceChoices;
    }

    /**
     * Add letter instance choice
     *
     * @param LetterInstanceChoice $choice
     * @return self
     */
    public function addLetterInstanceChoice(LetterInstanceChoice $choice)
    {
        if (!$this->letterInstanceChoices->contains($choice)) {
            $choice->setLetterInstance($this);
            $this->letterInstanceChoices->add($choice);
        }
        return $this;
    }

    /**
     * Get IDs of selected letter choices
     *
     * @return array<int>
     */
    public function getSelectedChoiceIds(): array
    {
        $ids = [];
        foreach ($this->letterInstanceChoices as $instanceChoice) {
            $ids[] = $instanceChoice->getLetterChoice()->getId();
        }
        return $ids;
    }

    /**
     * Get keys of selected letter choices
     *
     * @return array<string>
     */
    public function getSelectedChoiceKeys(): array
    {
        $keys = [];
        foreach ($this->letterInstanceChoices as $instanceChoice) {
            $keys[] = $instanceChoice->getLetterChoice()->getChoiceKey();
        }
        return $keys;
    }

    /**
     * Generate a unique reference
     *
     * @param string $prefix
     * @return string
     */
    public static function generateReference($prefix = 'LTR')
    {
        return $prefix . date('Ymd') . strtoupper(substr(uniqid(), -6));
    }
}

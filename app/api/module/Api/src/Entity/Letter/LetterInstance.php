<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LetterInstance Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_instance")
 */
class LetterInstance extends AbstractLetterInstance
{
    public const STATUS_DRAFT = 'letter_status_draft';
    public const STATUS_READY = 'letter_status_ready';
    public const STATUS_SENT = 'letter_status_sent';
    public const STATUS_FAILED = 'letter_status_failed';
    public const STATUS_CANCELLED = 'letter_status_cancelled';

    /**
     * Letter instance sections
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection",
     *     mappedBy="letterInstance",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     */
    protected $letterInstanceSections;

    /**
     * Letter instance issues
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue",
     *     mappedBy="letterInstance",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     */
    protected $letterInstanceIssues;

    /**
     * Letter instance todos
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo",
     *     mappedBy="letterInstance",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     */
    protected $letterInstanceTodos;

    /**
     * Letter instance appendices
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterInstanceAppendix",
     *     mappedBy="letterInstance",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     */
    protected $letterInstanceAppendices;

    /**
     * Initialise collections
     */
    public function __construct()
    {
        $this->letterInstanceSections = new ArrayCollection();
        $this->letterInstanceIssues = new ArrayCollection();
        $this->letterInstanceTodos = new ArrayCollection();
        $this->letterInstanceAppendices = new ArrayCollection();
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
        }
        return $this;
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
            if ($section->getLetterSectionVersion()->getRequiresInput() &&
                empty($section->getEditedContent())) {
                return false;
            }
        }

        foreach ($this->letterInstanceIssues as $issue) {
            if ($issue->getLetterIssueVersion()->getRequiresInput() &&
                empty($issue->getEditedContent())) {
                return false;
            }
        }

        return true;
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

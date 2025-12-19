<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * LetterType Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_type")
 */
class LetterType extends AbstractLetterType
{
    /**
     * Letter type sections
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterTypeSection",
     *     mappedBy="letterType",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     */
    protected $letterTypeSections;

    /**
     * Letter type issues
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterTypeIssue",
     *     mappedBy="letterType",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     */
    protected $letterTypeIssues;

    /**
     * Letter type appendices
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterTypeAppendix",
     *     mappedBy="letterType",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     */
    protected $letterTypeAppendices;

    /**
     * Initialise collections
     */
    public function __construct()
    {
        $this->letterTypeSections = new ArrayCollection();
        $this->letterTypeIssues = new ArrayCollection();
        $this->letterTypeAppendices = new ArrayCollection();
    }

    /**
     * Get letter type sections
     *
     * @return ArrayCollection
     */
    public function getLetterTypeSections()
    {
        return $this->letterTypeSections;
    }

    /**
     * Add letter type section
     *
     * @param LetterTypeSection $letterTypeSection
     * @return self
     */
    public function addLetterTypeSection(LetterTypeSection $letterTypeSection)
    {
        if (!$this->letterTypeSections->contains($letterTypeSection)) {
            $letterTypeSection->setLetterType($this);
            $this->letterTypeSections->add($letterTypeSection);
        }
        return $this;
    }

    /**
     * Remove letter type section
     *
     * @param LetterTypeSection $letterTypeSection
     * @return self
     */
    public function removeLetterTypeSection(LetterTypeSection $letterTypeSection)
    {
        $this->letterTypeSections->removeElement($letterTypeSection);
        return $this;
    }

    /**
     * Get letter type issues
     *
     * @return ArrayCollection
     */
    public function getLetterTypeIssues()
    {
        return $this->letterTypeIssues;
    }

    /**
     * Add letter type issue
     *
     * @param LetterTypeIssue $letterTypeIssue
     * @return self
     */
    public function addLetterTypeIssue(LetterTypeIssue $letterTypeIssue)
    {
        if (!$this->letterTypeIssues->contains($letterTypeIssue)) {
            $letterTypeIssue->setLetterType($this);
            $this->letterTypeIssues->add($letterTypeIssue);
        }
        return $this;
    }

    /**
     * Remove letter type issue
     *
     * @param LetterTypeIssue $letterTypeIssue
     * @return self
     */
    public function removeLetterTypeIssue(LetterTypeIssue $letterTypeIssue)
    {
        $this->letterTypeIssues->removeElement($letterTypeIssue);
        return $this;
    }

    /**
     * Get letter type appendices
     *
     * @return ArrayCollection
     */
    public function getLetterTypeAppendices()
    {
        return $this->letterTypeAppendices;
    }

    /**
     * Add letter type appendix
     *
     * @param LetterTypeAppendix $letterTypeAppendix
     * @return self
     */
    public function addLetterTypeAppendix(LetterTypeAppendix $letterTypeAppendix)
    {
        if (!$this->letterTypeAppendices->contains($letterTypeAppendix)) {
            $letterTypeAppendix->setLetterType($this);
            $this->letterTypeAppendices->add($letterTypeAppendix);
        }
        return $this;
    }

    /**
     * Remove letter type appendix
     *
     * @param LetterTypeAppendix $letterTypeAppendix
     * @return self
     */
    public function removeLetterTypeAppendix(LetterTypeAppendix $letterTypeAppendix)
    {
        $this->letterTypeAppendices->removeElement($letterTypeAppendix);
        return $this;
    }

    /**
     * Check if letter type is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * Activate letter type
     *
     * @return self
     */
    public function activate()
    {
        $this->isActive = true;
        return $this;
    }

    /**
     * Deactivate letter type
     *
     * @return self
     */
    public function deactivate()
    {
        $this->isActive = false;
        return $this;
    }
}

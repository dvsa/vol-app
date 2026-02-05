<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Generic;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractQuestionText Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="question_text",
 *    indexes={
 *        @ORM\Index(name="fk_question_text_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_question_text_last_modified_by_user_id", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_question_text_question_short_key_translation_key_id", columns={"question_short_key"}),
 *        @ORM\Index(name="ix_question_text_question_id", columns={"question_id"})
 *    }
 * )
 */
abstract class AbstractQuestionText implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Primary key.  Auto incremented if numeric.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Question
     *
     * @var \Dvsa\Olcs\Api\Entity\Generic\Question
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Generic\Question", fetch="LAZY")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     */
    protected $question;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="create")
     */
    protected $createdBy;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="update")
     */
    protected $lastModifiedBy;

    /**
     * Effective from
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="effective_from", nullable=true)
     */
    protected $effectiveFrom;

    /**
     * Question short key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="question_short_key", length=255, nullable=true)
     */
    protected $questionShortKey;

    /**
     * Question summary key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="question_summary_key", length=255, nullable=true)
     */
    protected $questionSummaryKey;

    /**
     * Question key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="question_key", length=255, nullable=true)
     */
    protected $questionKey;

    /**
     * Warning key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="warning_key", length=255, nullable=true)
     */
    protected $warningKey;

    /**
     * Details key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="details_key", length=255, nullable=true)
     */
    protected $detailsKey;

    /**
     * Guidance key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="guidance_key", length=1024, nullable=true)
     */
    protected $guidanceKey;

    /**
     * Additional guidance key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="additional_guidance_key", length=1024, nullable=true)
     */
    protected $additionalGuidanceKey;

    /**
     * Hint key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="hint_key", length=255, nullable=true)
     */
    protected $hintKey;

    /**
     * Bullet list key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="bullet_list_key", length=255, nullable=true)
     */
    protected $bulletListKey;

    /**
     * Label key
     *
     * @var string
     *
     * @ORM\Column(type="string", name="label_key", length=255, nullable=true)
     */
    protected $labelKey;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Initialise the collections
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise collections
     */
    public function initCollections(): void
    {
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return QuestionText
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the question
     *
     * @param \Dvsa\Olcs\Api\Entity\Generic\Question $question new value being set
     *
     * @return QuestionText
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get the question
     *
     * @return \Dvsa\Olcs\Api\Entity\Generic\Question     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return QuestionText
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return QuestionText
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the effective from
     *
     * @param \DateTime $effectiveFrom new value being set
     *
     * @return QuestionText
     */
    public function setEffectiveFrom($effectiveFrom)
    {
        $this->effectiveFrom = $effectiveFrom;

        return $this;
    }

    /**
     * Get the effective from
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getEffectiveFrom($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->effectiveFrom);
        }

        return $this->effectiveFrom;
    }

    /**
     * Set the question short key
     *
     * @param string $questionShortKey new value being set
     *
     * @return QuestionText
     */
    public function setQuestionShortKey($questionShortKey)
    {
        $this->questionShortKey = $questionShortKey;

        return $this;
    }

    /**
     * Get the question short key
     *
     * @return string     */
    public function getQuestionShortKey()
    {
        return $this->questionShortKey;
    }

    /**
     * Set the question summary key
     *
     * @param string $questionSummaryKey new value being set
     *
     * @return QuestionText
     */
    public function setQuestionSummaryKey($questionSummaryKey)
    {
        $this->questionSummaryKey = $questionSummaryKey;

        return $this;
    }

    /**
     * Get the question summary key
     *
     * @return string     */
    public function getQuestionSummaryKey()
    {
        return $this->questionSummaryKey;
    }

    /**
     * Set the question key
     *
     * @param string $questionKey new value being set
     *
     * @return QuestionText
     */
    public function setQuestionKey($questionKey)
    {
        $this->questionKey = $questionKey;

        return $this;
    }

    /**
     * Get the question key
     *
     * @return string     */
    public function getQuestionKey()
    {
        return $this->questionKey;
    }

    /**
     * Set the warning key
     *
     * @param string $warningKey new value being set
     *
     * @return QuestionText
     */
    public function setWarningKey($warningKey)
    {
        $this->warningKey = $warningKey;

        return $this;
    }

    /**
     * Get the warning key
     *
     * @return string     */
    public function getWarningKey()
    {
        return $this->warningKey;
    }

    /**
     * Set the details key
     *
     * @param string $detailsKey new value being set
     *
     * @return QuestionText
     */
    public function setDetailsKey($detailsKey)
    {
        $this->detailsKey = $detailsKey;

        return $this;
    }

    /**
     * Get the details key
     *
     * @return string     */
    public function getDetailsKey()
    {
        return $this->detailsKey;
    }

    /**
     * Set the guidance key
     *
     * @param string $guidanceKey new value being set
     *
     * @return QuestionText
     */
    public function setGuidanceKey($guidanceKey)
    {
        $this->guidanceKey = $guidanceKey;

        return $this;
    }

    /**
     * Get the guidance key
     *
     * @return string     */
    public function getGuidanceKey()
    {
        return $this->guidanceKey;
    }

    /**
     * Set the additional guidance key
     *
     * @param string $additionalGuidanceKey new value being set
     *
     * @return QuestionText
     */
    public function setAdditionalGuidanceKey($additionalGuidanceKey)
    {
        $this->additionalGuidanceKey = $additionalGuidanceKey;

        return $this;
    }

    /**
     * Get the additional guidance key
     *
     * @return string     */
    public function getAdditionalGuidanceKey()
    {
        return $this->additionalGuidanceKey;
    }

    /**
     * Set the hint key
     *
     * @param string $hintKey new value being set
     *
     * @return QuestionText
     */
    public function setHintKey($hintKey)
    {
        $this->hintKey = $hintKey;

        return $this;
    }

    /**
     * Get the hint key
     *
     * @return string     */
    public function getHintKey()
    {
        return $this->hintKey;
    }

    /**
     * Set the bullet list key
     *
     * @param string $bulletListKey new value being set
     *
     * @return QuestionText
     */
    public function setBulletListKey($bulletListKey)
    {
        $this->bulletListKey = $bulletListKey;

        return $this;
    }

    /**
     * Get the bullet list key
     *
     * @return string     */
    public function getBulletListKey()
    {
        return $this->bulletListKey;
    }

    /**
     * Set the label key
     *
     * @param string $labelKey new value being set
     *
     * @return QuestionText
     */
    public function setLabelKey($labelKey)
    {
        $this->labelKey = $labelKey;

        return $this;
    }

    /**
     * Get the label key
     *
     * @return string     */
    public function getLabelKey()
    {
        return $this->labelKey;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return QuestionText
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}

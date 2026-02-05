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
 * AbstractQuestion Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="question",
 *    indexes={
 *        @ORM\Index(name="fk_question_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_question_form_control_type_ref_data_id", columns={"form_control_type"}),
 *        @ORM\Index(name="fk_question_last_modified_by_user_id", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_question_question_type_ref_data_id", columns={"question_type"}),
 *        @ORM\Index(name="fk_question_submit_options_ref_data_id", columns={"submit_options"}),
 *        @ORM\Index(name="question_slug_uindex", columns={"slug"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="question_slug_uindex", columns={"slug"})
 *    }
 * )
 */
abstract class AbstractQuestion implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * QuestionType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="question_type", referencedColumnName="id", nullable=true)
     */
    protected $questionType;

    /**
     * FormControlType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="form_control_type", referencedColumnName="id", nullable=true)
     */
    protected $formControlType;

    /**
     * SubmitOptions
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="submit_options", referencedColumnName="id", nullable=true)
     */
    protected $submitOptions;

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
     * Title
     *
     * @var string
     *
     * @ORM\Column(type="string", name="title", length=100, nullable=true)
     */
    protected $title;

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=true)
     */
    protected $description;

    /**
     * Option source
     *
     * @var string
     *
     * @ORM\Column(type="string", name="option_source", length=4096, nullable=true)
     */
    protected $optionSource;

    /**
     * Slug
     *
     * @var string
     *
     * @ORM\Column(type="string", name="slug", length=255, nullable=true)
     */
    protected $slug;

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
     * ApplicationValidations
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Generic\ApplicationValidation", mappedBy="question")
     * @ORM\OrderBy({"weight" = "ASC"})
     */
    protected $applicationValidations;

    /**
     * QuestionTexts
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Generic\QuestionText", mappedBy="question")
     */
    protected $questionTexts;

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
        $this->applicationValidations = new ArrayCollection();
        $this->questionTexts = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Question
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
     * Set the question type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $questionType new value being set
     *
     * @return Question
     */
    public function setQuestionType($questionType)
    {
        $this->questionType = $questionType;

        return $this;
    }

    /**
     * Get the question type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getQuestionType()
    {
        return $this->questionType;
    }

    /**
     * Set the form control type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $formControlType new value being set
     *
     * @return Question
     */
    public function setFormControlType($formControlType)
    {
        $this->formControlType = $formControlType;

        return $this;
    }

    /**
     * Get the form control type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getFormControlType()
    {
        return $this->formControlType;
    }

    /**
     * Set the submit options
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $submitOptions new value being set
     *
     * @return Question
     */
    public function setSubmitOptions($submitOptions)
    {
        $this->submitOptions = $submitOptions;

        return $this;
    }

    /**
     * Get the submit options
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getSubmitOptions()
    {
        return $this->submitOptions;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return Question
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Question
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
     * Set the title
     *
     * @param string $title new value being set
     *
     * @return Question
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the title
     *
     * @return string     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return Question
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the option source
     *
     * @param string $optionSource new value being set
     *
     * @return Question
     */
    public function setOptionSource($optionSource)
    {
        $this->optionSource = $optionSource;

        return $this;
    }

    /**
     * Get the option source
     *
     * @return string     */
    public function getOptionSource()
    {
        return $this->optionSource;
    }

    /**
     * Set the slug
     *
     * @param string $slug new value being set
     *
     * @return Question
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get the slug
     *
     * @return string     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Question
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
     * Set the application validations
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $applicationValidations collection being set as the value
     *
     * @return Question
     */
    public function setApplicationValidations($applicationValidations)
    {
        $this->applicationValidations = $applicationValidations;

        return $this;
    }

    /**
     * Get the application validations
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getApplicationValidations()
    {
        return $this->applicationValidations;
    }

    /**
     * Add a application validations
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $applicationValidations collection being added
     *
     * @return Question
     */
    public function addApplicationValidations($applicationValidations)
    {
        if ($applicationValidations instanceof ArrayCollection) {
            $this->applicationValidations = new ArrayCollection(
                array_merge(
                    $this->applicationValidations->toArray(),
                    $applicationValidations->toArray()
                )
            );
        } elseif (!$this->applicationValidations->contains($applicationValidations)) {
            $this->applicationValidations->add($applicationValidations);
        }

        return $this;
    }

    /**
     * Remove a application validations
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $applicationValidations collection being removed
     *
     * @return Question
     */
    public function removeApplicationValidations($applicationValidations)
    {
        if ($this->applicationValidations->contains($applicationValidations)) {
            $this->applicationValidations->removeElement($applicationValidations);
        }

        return $this;
    }

    /**
     * Set the question texts
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $questionTexts collection being set as the value
     *
     * @return Question
     */
    public function setQuestionTexts($questionTexts)
    {
        $this->questionTexts = $questionTexts;

        return $this;
    }

    /**
     * Get the question texts
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getQuestionTexts()
    {
        return $this->questionTexts;
    }

    /**
     * Add a question texts
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $questionTexts collection being added
     *
     * @return Question
     */
    public function addQuestionTexts($questionTexts)
    {
        if ($questionTexts instanceof ArrayCollection) {
            $this->questionTexts = new ArrayCollection(
                array_merge(
                    $this->questionTexts->toArray(),
                    $questionTexts->toArray()
                )
            );
        } elseif (!$this->questionTexts->contains($questionTexts)) {
            $this->questionTexts->add($questionTexts);
        }

        return $this;
    }

    /**
     * Remove a question texts
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $questionTexts collection being removed
     *
     * @return Question
     */
    public function removeQuestionTexts($questionTexts)
    {
        if ($this->questionTexts->contains($questionTexts)) {
            $this->questionTexts->removeElement($questionTexts);
        }

        return $this;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}

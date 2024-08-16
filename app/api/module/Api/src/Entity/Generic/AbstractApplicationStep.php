<?php

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
 * ApplicationStep Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="application_step",
 *    indexes={
 *        @ORM\Index(name="fk_application_step_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_application_step_last_modified_by_user_id",
     *     columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_application_step_application_path_id", columns={"application_path_id"}),
 *        @ORM\Index(name="ix_application_step_question_id", columns={"question_id"})
 *    }
 * )
 */
abstract class AbstractApplicationStep implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Application path
     *
     * @var \Dvsa\Olcs\Api\Entity\Generic\ApplicationPath
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Generic\ApplicationPath",
     *     fetch="LAZY",
     *     inversedBy="applicationSteps"
     * )
     * @ORM\JoinColumn(name="application_path_id", referencedColumnName="id", nullable=false)
     */
    protected $applicationPath;

    /**
     * Break on failure
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="break_on_failure", nullable=true)
     */
    protected $breakOnFailure;

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
     * Enabled after submission
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean",
     *     name="enabled_after_submission",
     *     nullable=true,
     *     options={"default": 0})
     */
    protected $enabledAfterSubmission = 0;

    /**
     * Identifier - Id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Ignore question validation
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="ignore_question_validation", nullable=true)
     */
    protected $ignoreQuestionValidation;

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
     * Only on yes
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="only_on_yes", nullable=true)
     */
    protected $onlyOnYes;

    /**
     * Question
     *
     * @var \Dvsa\Olcs\Api\Entity\Generic\Question
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Generic\Question", fetch="LAZY")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id", nullable=false)
     */
    protected $question;

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
     * Weight
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="weight", precision=10, scale=2, nullable=true)
     */
    protected $weight;

    /**
     * Application validation
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Generic\ApplicationValidation",
     *     mappedBy="applicationStep"
     * )
     */
    protected $applicationValidations;

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function initCollections()
    {
        $this->applicationValidations = new ArrayCollection();
    }

    /**
     * Set the application path
     *
     * @param \Dvsa\Olcs\Api\Entity\Generic\ApplicationPath $applicationPath entity being set as the value
     *
     * @return ApplicationStep
     */
    public function setApplicationPath($applicationPath)
    {
        $this->applicationPath = $applicationPath;

        return $this;
    }

    /**
     * Get the application path
     *
     * @return \Dvsa\Olcs\Api\Entity\Generic\ApplicationPath
     */
    public function getApplicationPath()
    {
        return $this->applicationPath;
    }

    /**
     * Set the break on failure
     *
     * @param boolean $breakOnFailure new value being set
     *
     * @return ApplicationStep
     */
    public function setBreakOnFailure($breakOnFailure)
    {
        $this->breakOnFailure = $breakOnFailure;

        return $this;
    }

    /**
     * Get the break on failure
     *
     * @return boolean
     */
    public function getBreakOnFailure()
    {
        return $this->breakOnFailure;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return ApplicationStep
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the enabled after submission
     *
     * @param boolean $enabledAfterSubmission new value being set
     *
     * @return ApplicationStep
     */
    public function setEnabledAfterSubmission($enabledAfterSubmission)
    {
        $this->enabledAfterSubmission = $enabledAfterSubmission;

        return $this;
    }

    /**
     * Get the enabled after submission
     *
     * @return boolean
     */
    public function getEnabledAfterSubmission()
    {
        return $this->enabledAfterSubmission;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return ApplicationStep
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the ignore question validation
     *
     * @param boolean $ignoreQuestionValidation new value being set
     *
     * @return ApplicationStep
     */
    public function setIgnoreQuestionValidation($ignoreQuestionValidation)
    {
        $this->ignoreQuestionValidation = $ignoreQuestionValidation;

        return $this;
    }

    /**
     * Get the ignore question validation
     *
     * @return boolean
     */
    public function getIgnoreQuestionValidation()
    {
        return $this->ignoreQuestionValidation;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return ApplicationStep
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the only on yes
     *
     * @param boolean $onlyOnYes new value being set
     *
     * @return ApplicationStep
     */
    public function setOnlyOnYes($onlyOnYes)
    {
        $this->onlyOnYes = $onlyOnYes;

        return $this;
    }

    /**
     * Get the only on yes
     *
     * @return boolean
     */
    public function getOnlyOnYes()
    {
        return $this->onlyOnYes;
    }

    /**
     * Set the question
     *
     * @param \Dvsa\Olcs\Api\Entity\Generic\Question $question entity being set as the value
     *
     * @return ApplicationStep
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get the question
     *
     * @return \Dvsa\Olcs\Api\Entity\Generic\Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return ApplicationStep
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the weight
     *
     * @param float $weight new value being set
     *
     * @return ApplicationStep
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get the weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set the application validation
     *
     * @param ArrayCollection $applicationValidations collection being set as the value
     *
     * @return ApplicationStep
     */
    public function setApplicationValidations($applicationValidations)
    {
        $this->applicationValidations = $applicationValidations;

        return $this;
    }

    /**
     * Get the application validations
     *
     * @return ArrayCollection
     */
    public function getApplicationValidations()
    {
        return $this->applicationValidations;
    }

    /**
     * Add a application validations
     *
     * @param ArrayCollection|mixed $applicationValidations collection being added
     *
     * @return ApplicationStep
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
     * @return ApplicationStep
     */
    public function removeApplicationValidations($applicationValidations)
    {
        if ($this->applicationValidations->contains($applicationValidations)) {
            $this->applicationValidations->removeElement($applicationValidations);
        }

        return $this;
    }
}

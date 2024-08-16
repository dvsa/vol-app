<?php

namespace Dvsa\Olcs\Api\Entity\Generic;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Answer Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="answer",
 *    indexes={
 *        @ORM\Index(name="IDX_DADD4A25DC41CE69", columns={"irhp_application_id"}),
 *        @ORM\Index(name="fk_answer_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_answer_irhp_permit_application_id_irhp_permit_application_id",
     *     columns={"irhp_permit_application_id"}),
 *        @ORM\Index(name="fk_answer_last_modified_by_user_id", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_answer_question_text_id", columns={"question_text_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_answer_irhp_application_id_question_text_id",
     *     columns={"irhp_application_id","irhp_permit_application_id","question_text_id"})
 *    }
 * )
 */
abstract class AbstractAnswer implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Ans array
     *
     * @var string
     *
     * @ORM\Column(type="text", name="ans_array", length=0, nullable=true)
     */
    protected $ansArray;

    /**
     * Ans boolean
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="ans_boolean", nullable=true)
     */
    protected $ansBoolean;

    /**
     * Ans date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="ans_date", nullable=true)
     */
    protected $ansDate;

    /**
     * Ans datetime
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="ans_datetime", nullable=true)
     */
    protected $ansDatetime;

    /**
     * Ans decimal
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="ans_decimal", precision=18, scale=4, nullable=true)
     */
    protected $ansDecimal;

    /**
     * Ans integer
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="ans_integer", nullable=true)
     */
    protected $ansInteger;

    /**
     * Ans string
     *
     * @var string
     *
     * @ORM\Column(type="string", name="ans_string", length=255, nullable=true)
     */
    protected $ansString;

    /**
     * Ans text
     *
     * @var string
     *
     * @ORM\Column(type="text", name="ans_text", length=0, nullable=true)
     */
    protected $ansText;

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
     * Irhp application
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpApplication",
     *     fetch="LAZY",
     *     inversedBy="answers"
     * )
     * @ORM\JoinColumn(name="irhp_application_id", referencedColumnName="id", nullable=true)
     */
    protected $irhpApplication;

    /**
     * Irhp permit application
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication",
     *     fetch="LAZY",
     *     inversedBy="answers"
     * )
     * @ORM\JoinColumn(name="irhp_permit_application_id", referencedColumnName="id", nullable=true)
     */
    protected $irhpPermitApplication;

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
     * Question text
     *
     * @var \Dvsa\Olcs\Api\Entity\Generic\QuestionText
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Generic\QuestionText", fetch="LAZY")
     * @ORM\JoinColumn(name="question_text_id", referencedColumnName="id", nullable=false)
     */
    protected $questionText;

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
     * Set the ans array
     *
     * @param string $ansArray new value being set
     *
     * @return Answer
     */
    public function setAnsArray($ansArray)
    {
        $this->ansArray = $ansArray;

        return $this;
    }

    /**
     * Get the ans array
     *
     * @return string
     */
    public function getAnsArray()
    {
        return $this->ansArray;
    }

    /**
     * Set the ans boolean
     *
     * @param boolean $ansBoolean new value being set
     *
     * @return Answer
     */
    public function setAnsBoolean($ansBoolean)
    {
        $this->ansBoolean = $ansBoolean;

        return $this;
    }

    /**
     * Get the ans boolean
     *
     * @return boolean
     */
    public function getAnsBoolean()
    {
        return $this->ansBoolean;
    }

    /**
     * Set the ans date
     *
     * @param \DateTime $ansDate new value being set
     *
     * @return Answer
     */
    public function setAnsDate($ansDate)
    {
        $this->ansDate = $ansDate;

        return $this;
    }

    /**
     * Get the ans date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getAnsDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->ansDate);
        }

        return $this->ansDate;
    }

    /**
     * Set the ans datetime
     *
     * @param \DateTime $ansDatetime new value being set
     *
     * @return Answer
     */
    public function setAnsDatetime($ansDatetime)
    {
        $this->ansDatetime = $ansDatetime;

        return $this;
    }

    /**
     * Get the ans datetime
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getAnsDatetime($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->ansDatetime);
        }

        return $this->ansDatetime;
    }

    /**
     * Set the ans decimal
     *
     * @param float $ansDecimal new value being set
     *
     * @return Answer
     */
    public function setAnsDecimal($ansDecimal)
    {
        $this->ansDecimal = $ansDecimal;

        return $this;
    }

    /**
     * Get the ans decimal
     *
     * @return float
     */
    public function getAnsDecimal()
    {
        return $this->ansDecimal;
    }

    /**
     * Set the ans integer
     *
     * @param int $ansInteger new value being set
     *
     * @return Answer
     */
    public function setAnsInteger($ansInteger)
    {
        $this->ansInteger = $ansInteger;

        return $this;
    }

    /**
     * Get the ans integer
     *
     * @return int
     */
    public function getAnsInteger()
    {
        return $this->ansInteger;
    }

    /**
     * Set the ans string
     *
     * @param string $ansString new value being set
     *
     * @return Answer
     */
    public function setAnsString($ansString)
    {
        $this->ansString = $ansString;

        return $this;
    }

    /**
     * Get the ans string
     *
     * @return string
     */
    public function getAnsString()
    {
        return $this->ansString;
    }

    /**
     * Set the ans text
     *
     * @param string $ansText new value being set
     *
     * @return Answer
     */
    public function setAnsText($ansText)
    {
        $this->ansText = $ansText;

        return $this;
    }

    /**
     * Get the ans text
     *
     * @return string
     */
    public function getAnsText()
    {
        return $this->ansText;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Answer
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
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Answer
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
     * Set the irhp application
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication $irhpApplication entity being set as the value
     *
     * @return Answer
     */
    public function setIrhpApplication($irhpApplication)
    {
        $this->irhpApplication = $irhpApplication;

        return $this;
    }

    /**
     * Get the irhp application
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication
     */
    public function getIrhpApplication()
    {
        return $this->irhpApplication;
    }

    /**
     * Set the irhp permit application
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication $irhpPermitApplication entity being set as the value
     *
     * @return Answer
     */
    public function setIrhpPermitApplication($irhpPermitApplication)
    {
        $this->irhpPermitApplication = $irhpPermitApplication;

        return $this;
    }

    /**
     * Get the irhp permit application
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication
     */
    public function getIrhpPermitApplication()
    {
        return $this->irhpPermitApplication;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return Answer
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
     * Set the question text
     *
     * @param \Dvsa\Olcs\Api\Entity\Generic\QuestionText $questionText entity being set as the value
     *
     * @return Answer
     */
    public function setQuestionText($questionText)
    {
        $this->questionText = $questionText;

        return $this;
    }

    /**
     * Get the question text
     *
     * @return \Dvsa\Olcs\Api\Entity\Generic\QuestionText
     */
    public function getQuestionText()
    {
        return $this->questionText;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Answer
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
}

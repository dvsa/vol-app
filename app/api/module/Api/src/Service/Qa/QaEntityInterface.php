<?php

namespace Dvsa\Olcs\Api\Service\Qa;

use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPath;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Generic\QuestionText;

interface QaEntityInterface
{
    /**
     * Return id of the entity
     *
     * @return int
     */
    public function getId();

    /**
     * Return additional Q&A view data applicable to this entity type
     *
     *
     * @return array
     */
    public function getAdditionalQaViewData(ApplicationStep $applicationStep);

    /**
     * Executed on submission of an application step
     */
    public function onSubmitApplicationStep();

    /**
     * Create an answer entity relating to the implementing class
     *
     * @param QuestionText $questionText
     *
     * return Answer
     */
    public function createAnswer(QuestionText $questionText);

    /**
     * Get an answer to the given application step
     *
     * @return mixed|null
     */
    public function getAnswer(ApplicationStep $applicationStep);

    /**
     * Return the entity name in camel case
     *
     * @return string
     */
    public function getCamelCaseEntityName();

    /**
     * Get the active application path
     *
     * @return ApplicationPath|null
     */
    public function getActiveApplicationPath();

    /**
     * @return bool
     */
    public function isNotYetSubmitted();

    /**
     * Whether this entity supports the Q&A mechanism
     *
     * @return bool
     */
    public function isApplicationPathEnabled();

    /**
     * The repository name associated with this entity
     *
     * @return string
     */
    public function getRepositoryName();
}

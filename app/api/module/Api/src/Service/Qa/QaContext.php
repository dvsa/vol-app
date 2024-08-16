<?php

namespace Dvsa\Olcs\Api\Service\Qa;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;

class QaContext
{
    /**
     * Create instance
     *
     *
     * @return QaContext
     */
    public function __construct(private readonly ApplicationStepEntity $applicationStepEntity, private readonly QaEntityInterface $qaEntity)
    {
    }

    /**
     * Get the embedded ApplicationStep instance
     *
     * @return ApplicationStepEntity
     */
    public function getApplicationStepEntity()
    {
        return $this->applicationStepEntity;
    }

    /**
     * Get the embedded QaEntityInterface instance
     *
     * @return QaEntityInterface
     */
    public function getQaEntity()
    {
        return $this->qaEntity;
    }

    /**
     * Get the answer value associated with this context
     *
     * @return mixed|null
     */
    public function getAnswerValue()
    {
        return $this->qaEntity->getAnswer($this->applicationStepEntity);
    }

    /**
     * Whether the application step within this qa context should show as enabled
     *
     * @return bool
     */
    public function isApplicationStepEnabled()
    {
        if ($this->qaEntity->isNotYetSubmitted() || $this->qaEntity->isUnderConsideration()) {
            return true;
        }

        return $this->applicationStepEntity->getEnabledAfterSubmission();
    }
}

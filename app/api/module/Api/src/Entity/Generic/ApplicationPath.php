<?php

namespace Dvsa\Olcs\Api\Entity\Generic;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;

/**
 * ApplicationPath Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="application_path",
 *    indexes={
 *        @ORM\Index(name="fk_application_path_irhp_permit_type_id", columns={"irhp_permit_type_id"})
 *    }
 * )
 */
class ApplicationPath extends AbstractApplicationPath
{
    /**
     * Get the answer value corresponding to the specified question id
     *
     * @param int $id
     *
     * @return mixed|null
     */
    public function getAnswerValueByQuestionId($id, QaEntityInterface $qaEntity)
    {
        $applicationStep = $this->getApplicationStepByQuestionId($id);
        if (!is_null($applicationStep)) {
            return $qaEntity->getAnswer($applicationStep);
        }

        return null;
    }

    /**
     * Get the application step corresponding to the specified question id
     *
     * @param int $id
     *
     * @return ApplicationStep|null
     */
    public function getApplicationStepByQuestionId($id)
    {
        /** @var ApplicationStep $applicationStep */
        foreach ($this->applicationSteps as $applicationStep) {
            if ($applicationStep->getQuestion()->getId() == $id) {
                return $applicationStep;
            }
        }

        return null;
    }
}

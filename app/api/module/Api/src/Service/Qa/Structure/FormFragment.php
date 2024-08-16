<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

class FormFragment
{
    /** @var array */
    private $applicationSteps = [];

    /**
     * Add an application step to this form fragment
     */
    public function addApplicationStep(ApplicationStep $applicationStep)
    {
        $this->applicationSteps[] = $applicationStep;
    }

    /**
     * Get the representation of this class to be returned by the API endpoint
     *
     * @return array
     */
    public function getRepresentation()
    {
        $response = [];

        foreach ($this->applicationSteps as $applicationStep) {
            $response[] = $applicationStep->getRepresentation();
        }

        return $response;
    }
}

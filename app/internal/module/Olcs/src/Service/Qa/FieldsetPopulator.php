<?php

namespace Olcs\Service\Qa;

use Common\Service\Qa\FieldsetAdder;

class FieldsetPopulator
{
    /** @var FieldsetAdder */
    private $fieldsetAdder;

    /**
     * Create service instance
     *
     * @param FieldsetAdder $fieldsetAdder
     *
     * @return FieldsetPopulator
     */
    public function __construct(FieldsetAdder $fieldsetAdder)
    {
        $this->fieldsetAdder = $fieldsetAdder;
    }

    public function populate($form, array $applicationSteps)
    {
        foreach ($applicationSteps as $applicationStep) {
            $this->fieldsetAdder->add($form, $applicationStep);
        }
    }
}

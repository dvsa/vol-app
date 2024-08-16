<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContextFactory;

class FormFragmentGenerator
{
    /**
     * Create service instance
     *
     *
     * @return FormFragmentGenerator
     */
    public function __construct(private readonly FormFragmentFactory $formFragmentFactory, private readonly ApplicationStepGenerator $applicationStepGenerator, private readonly QaContextFactory $qaContextFactory)
    {
    }

    /**
     * Build and return a FormFragment instance using the appropriate data sources
     *
     *
     * @return FormFragment
     */
    public function generate(array $applicationStepEntities, IrhpApplicationEntity $irhpApplicationEntity)
    {
        $formFragment = $this->formFragmentFactory->create();

        foreach ($applicationStepEntities as $applicationStepEntity) {
            $qaContext = $this->qaContextFactory->create($applicationStepEntity, $irhpApplicationEntity);

            $formFragment->addApplicationStep(
                $this->applicationStepGenerator->generate($qaContext, ElementContainer::FORM_FRAGMENT)
            );
        }

        return $formFragment;
    }
}

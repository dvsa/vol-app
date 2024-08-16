<?php

namespace Dvsa\Olcs\Api\Domain;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ConfigInterface;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Strategy\FormControlStrategyInterface;

/**
 * @template-extends AbstractPluginManager<FormControlStrategyInterface>
 */
class FormControlServiceManager extends AbstractPluginManager
{
    protected $instanceOf = FormControlStrategyInterface::class;

    /**
     * Get the form control strategy corresponding to the provided application step
     *
     *
     * @return FormControlStrategyInterface
     */
    public function getByApplicationStep(ApplicationStep $applicationStep)
    {
        return $this->get(
            $applicationStep->getQuestion()->getFormControlType()->getId()
        );
    }
}

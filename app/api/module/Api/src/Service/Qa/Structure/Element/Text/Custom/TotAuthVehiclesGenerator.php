<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\TextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpApplicationOnlyTrait;

class TotAuthVehiclesGenerator implements ElementGeneratorInterface
{
    use IrhpApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return TotAuthVehiclesGenerator
     */
    public function __construct(private TextGenerator $textGenerator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ElementGeneratorContext $context)
    {
        $textElement = $this->textGenerator->generate($context);

        $totAuthVehicles = $context->getQaEntity()->getLicence()->getTotAuthVehicles();

        $betweenRule = $context->getValidatorList()->getValidatorByRule('LessThan');
        $betweenRule->setParameter('max', $totAuthVehicles);

        $label = $textElement->getHint();
        $label->getParameter(0)->setValue($totAuthVehicles);

        return $textElement;
    }
}

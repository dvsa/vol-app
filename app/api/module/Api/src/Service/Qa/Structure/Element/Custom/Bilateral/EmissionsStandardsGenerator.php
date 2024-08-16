<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class EmissionsStandardsGenerator implements ElementGeneratorInterface
{
    use IrhpPermitApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return EmissionsStandardsGenerator
     */
    public function __construct(private EmissionsStandardsFactory $emissionsStandardsFactory)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ElementGeneratorContext $context)
    {
        $yesNo = null;
        if ($context->getAnswerValue()) {
            $yesNo = 'Y';
        }

        return $this->emissionsStandardsFactory->create($yesNo);
    }
}

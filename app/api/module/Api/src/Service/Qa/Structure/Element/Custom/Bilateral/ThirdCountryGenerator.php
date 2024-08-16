<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class ThirdCountryGenerator implements ElementGeneratorInterface
{
    use IrhpPermitApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return ThirdCountryGenerator
     */
    public function __construct(private ThirdCountryFactory $thirdCountryFactory)
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

        return $this->thirdCountryFactory->create($yesNo);
    }
}

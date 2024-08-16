<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\EcmtRemoval;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\DateWithThresholdGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\AnyTrait;

class PermitStartDateGenerator implements ElementGeneratorInterface
{
    use AnyTrait;

    public const DATE_THRESHOLD = 'P60D';

    /**
     * Create service instance
     *
     *
     * @return PermitStartDateGenerator
     */
    public function __construct(private DateWithThresholdGenerator $dateWithThresholdGenerator)
    {
    }

    /**
     * Build and return an element instance using the appropriate data sources
     *
     * @param ElementGeneratorContext $context
     *
     * @return ElementInterface
     */
    public function generate(ElementGeneratorContext $context)
    {
        return $this->dateWithThresholdGenerator->generate($context, self::DATE_THRESHOLD);
    }
}

<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common;

use Dvsa\Olcs\Api\Service\Common\CurrentDateTimeFactory;
use Dvsa\Olcs\Api\Service\Qa\Common\DateIntervalFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date\DateGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;

class DateWithThresholdGenerator
{
    /**
     * Create service instance
     *
     *
     * @return DateWithThresholdGenerator
     */
    public function __construct(private readonly DateWithThresholdFactory $dateWithThresholdFactory, private readonly CurrentDateTimeFactory $currentDateTimeFactory, private readonly DateIntervalFactory $dateIntervalFactory, private readonly DateGenerator $dateGenerator)
    {
    }

    /**
     * Build and return an element instance using the appropriate data sources
     *
     * @param string $dateInterval
     * @return DateWithThreshold
     */
    public function generate(ElementGeneratorContext $context, $dateInterval)
    {
        $dateThreshold = $this->currentDateTimeFactory->create();

        $dateThreshold->add(
            $this->dateIntervalFactory->create($dateInterval)
        );

        return $this->dateWithThresholdFactory->create(
            $dateThreshold,
            $this->dateGenerator->generate($context)
        );
    }
}

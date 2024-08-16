<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\CertRoadworthiness;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\DateWithThresholdGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpApplicationOnlyTrait;

class MotExpiryDateGenerator implements ElementGeneratorInterface
{
    use IrhpApplicationOnlyTrait;

    public const DATE_THRESHOLD = 'P14M';

    /**
     * Create service instance
     *
     *
     * @return MotExpiryDateGenerator
     */
    public function __construct(private MotExpiryDateFactory $motExpiryDateFactory, private DateWithThresholdGenerator $dateWithThresholdGenerator)
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
        $irhpApplication = $context->getQaEntity();
        $enableFileUploads = $context->isSelfservePageContainer() && $irhpApplication->getLicence()->isNi();

        return $this->motExpiryDateFactory->create(
            $enableFileUploads,
            $this->dateWithThresholdGenerator->generate($context, self::DATE_THRESHOLD)
        );
    }
}

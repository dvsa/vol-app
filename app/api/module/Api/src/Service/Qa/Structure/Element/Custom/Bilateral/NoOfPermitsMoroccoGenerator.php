<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class NoOfPermitsMoroccoGenerator implements ElementGeneratorInterface
{
    use IrhpPermitApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsMoroccoGenerator
     */
    public function __construct(private NoOfPermitsMoroccoFactory $noOfPermitsMoroccoFactory)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ElementGeneratorContext $context)
    {
        $irhpPermitApplication = $context->getQaEntity();

        $label = $irhpPermitApplication->getIrhpPermitWindow()
            ->getIrhpPermitStock()
            ->getPeriodNameKey();

        $bilateralRequired = $irhpPermitApplication->getFilteredBilateralRequired();
        $value = $bilateralRequired[IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED];

        return $this->noOfPermitsMoroccoFactory->create($label, $value);
    }
}

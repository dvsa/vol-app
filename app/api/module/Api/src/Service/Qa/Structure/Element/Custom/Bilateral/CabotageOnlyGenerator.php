<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class CabotageOnlyGenerator implements ElementGeneratorInterface
{
    use IrhpPermitApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return CabotageOnlyGenerator
     */
    public function __construct(private CabotageOnlyFactory $cabotageOnlyFactory)
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

        $irhpPermitApplication = $context->getQaEntity();

        $countryName = $irhpPermitApplication->getIrhpPermitWindow()
            ->getIrhpPermitStock()
            ->getCountry()
            ->getCountryDesc();

        return $this->cabotageOnlyFactory->create($yesNo, $countryName);
    }
}

<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use RuntimeException;

class StandardFieldsGenerator implements FieldsGeneratorInterface
{
    /**
     * Create service instance
     *
     *
     * @return StandardFieldsGenerator
     */
    public function __construct(private readonly CurrentFieldValuesGenerator $currentFieldValuesGenerator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(IrhpPermitStock $irhpPermitStock, ?IrhpPermitApplication $irhpPermitApplication)
    {
        $applicationPathGroupId = $irhpPermitStock->getApplicationPathGroup()->getId();
        $cabotageOptions = match ($applicationPathGroupId) {
            ApplicationPathGroup::BILATERALS_CABOTAGE_PERMITS_ONLY_ID => [
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED
            ],
            ApplicationPathGroup::BILATERALS_STANDARD_PERMITS_ONLY_ID, ApplicationPathGroup::BILATERALS_TURKEY_ID, ApplicationPathGroup::BILATERALS_UKRAINE_ID => [
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED
            ],
            ApplicationPathGroup::BILATERALS_STANDARD_AND_CABOTAGE_PERMITS_ID => [
                IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED,
                IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED
            ],
            default => throw new RuntimeException('Unexpected application path group'),
        };

        $currentFieldValues = $this->currentFieldValuesGenerator->generate($irhpPermitStock, $irhpPermitApplication);

        $journeyOptions = $irhpPermitStock->getPermitUsageList();

        $responseFields = [];
        foreach ($cabotageOptions as $cabotageOption) {
            foreach ($journeyOptions as $journeyOption) {
                $journeyOptionId = $journeyOption->getId();

                $responseFields[] = [
                    'journey' => $journeyOptionId,
                    'cabotage' => $cabotageOption,
                    'value' => $currentFieldValues[$journeyOptionId][$cabotageOption],
                ];
            }
        }

        return $responseFields;
    }
}

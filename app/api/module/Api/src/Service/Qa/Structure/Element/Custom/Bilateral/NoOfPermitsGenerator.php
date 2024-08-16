<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsAvailableTextboxes as AvailableTextboxes;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class NoOfPermitsGenerator implements ElementGeneratorInterface
{
    use IrhpPermitApplicationOnlyTrait;

    public const PERMIT_USAGE_KEY_LOOKUP = [
        RefData::JOURNEY_SINGLE => 'single',
        RefData::JOURNEY_MULTIPLE => 'multiple'
    ];

    public const STANDARD_OR_CABOTAGE_KEY_LOOKUP = [
        IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 'standard',
        IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 'cabotage'
    ];

    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsGenerator
     */
    public function __construct(private NoOfPermitsFactory $noOfPermitsFactory, private NoOfPermitsTextFactory $noOfPermitsTextFactory)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ElementGeneratorContext $context)
    {
        $irhpPermitApplication = $context->getQaEntity();

        $permitUsageAnswer = $irhpPermitApplication->getBilateralPermitUsageSelection();
        $permitUsageKey = self::PERMIT_USAGE_KEY_LOOKUP[$permitUsageAnswer];

        $cabotageAnswer = $irhpPermitApplication->getBilateralCabotageSelection();
        $availableTextboxes = AvailableTextBoxes::LOOKUP[$cabotageAnswer];

        $required = $irhpPermitApplication->getBilateralRequired();

        $noOfPermits = $this->noOfPermitsFactory->create();
        foreach ($availableTextboxes as $standardOrCabotage) {
            $standardOrCabotageKey = self::STANDARD_OR_CABOTAGE_KEY_LOOKUP[$standardOrCabotage];

            $hint = $this->generateTranslationKey('hint', $standardOrCabotageKey, $permitUsageKey);
            if ($irhpPermitApplication->isAssociatedWithBilateralCabotageOnlyApplicationPathGroup()) {
                $hint = 'qanda.bilaterals.number-of-permits.hint.cabotage.pre-october-2021';
            }

            $label = $this->generateTranslationKey('label', $standardOrCabotageKey, $permitUsageKey);
            $value = $required[$standardOrCabotage];

            $noOfPermits->addText(
                $this->noOfPermitsTextFactory->create($standardOrCabotage, $label, $hint, $value)
            );
        }

        return $noOfPermits;
    }

    /**
     * Create a translation key for use as a label or hint on a textbox
     *
     * @param string $purpose
     * @param string $standardOrCabotageKey
     * @param string $permitUsageKey
     *
     * @return string
     */
    private function generateTranslationKey($purpose, $standardOrCabotageKey, $permitUsageKey)
    {
        return sprintf(
            'qanda.bilaterals.number-of-permits.%s.%s.%s',
            $purpose,
            $standardOrCabotageKey,
            $permitUsageKey
        );
    }
}

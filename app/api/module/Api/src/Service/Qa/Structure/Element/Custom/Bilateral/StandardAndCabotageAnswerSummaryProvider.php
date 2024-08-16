<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AlwaysIncludeSlugTrait;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswerSummaryProviderInterface;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class StandardAndCabotageAnswerSummaryProvider implements AnswerSummaryProviderInterface
{
    use AlwaysIncludeSlugTrait;
    use IrhpPermitApplicationOnlyTrait;

    public const TEMPLATE_VARIABLES_LOOKUP = [
        Answer::BILATERAL_CABOTAGE_ONLY => [
            'yesNo' => 'qanda.bilaterals.cabotage.yes-answer',
            'additionalInfo' => Answer::BILATERAL_CABOTAGE_ONLY,
        ],
        Answer::BILATERAL_STANDARD_AND_CABOTAGE => [
            'yesNo' => 'qanda.bilaterals.cabotage.yes-answer',
            'additionalInfo' => Answer::BILATERAL_STANDARD_AND_CABOTAGE,
        ],
        Answer::BILATERAL_STANDARD_ONLY => [
            'yesNo' => 'qanda.bilaterals.cabotage.no-answer',
            'additionalInfo' => null,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return 'bilateral-standard-and-cabotage';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateVariables(QaContext $qaContext, ElementInterface $element, $isSnapshot)
    {
        $cabotageSelection = $qaContext->getQaEntity()->getBilateralCabotageSelection();
        return self::TEMPLATE_VARIABLES_LOOKUP[$cabotageSelection];
    }
}

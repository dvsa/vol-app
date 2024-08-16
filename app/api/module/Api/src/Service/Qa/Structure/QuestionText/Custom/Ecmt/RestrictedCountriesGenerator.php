<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\Custom\Ecmt;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpApplicationOnlyTrait;
use RuntimeException;

class RestrictedCountriesGenerator implements QuestionTextGeneratorInterface
{
    use IrhpApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return RestrictedCountriesGenerator
     */
    public function __construct(private QuestionTextGenerator $questionTextGenerator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(QaContext $qaContext)
    {
        $translationKeyFragmentMappings = [
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT => 'ecmt-annual',
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM => 'ecmt-short-term',
        ];

        $irhpApplicationEntity = $qaContext->getQaEntity();
        $irhpPermitTypeId = $irhpApplicationEntity->getIrhpPermitType()->getId();
        if (!array_key_exists($irhpPermitTypeId, $translationKeyFragmentMappings)) {
            throw new RuntimeException('This question does not support permit type ' . $irhpPermitTypeId);
        }

        $translationKeyFragment = $translationKeyFragmentMappings[$irhpPermitTypeId];

        $questionKey = sprintf('qanda.%s.restricted-countries.question', $translationKeyFragment);
        $questionSummaryKey = sprintf('qanda.%s.restricted-countries.question-summary', $translationKeyFragment);

        $associatedStock = $irhpApplicationEntity->getAssociatedStock();
        $excludedRestrictedCountryIds = $associatedStock->getExcludedRestrictedCountryIds();

        if (!empty($excludedRestrictedCountryIds)) {
            $suffix = '.without.' . implode('.', $excludedRestrictedCountryIds);
            $questionKey .= $suffix;
            $questionSummaryKey .= $suffix;
        }

        $applicationPathGroupId = $associatedStock->getApplicationPathGroup()->getId();

        if ($applicationPathGroupId == ApplicationPathGroup::ECMT_SHORT_TERM_2020_APSG_WITHOUT_SECTORS_ID) {
            $questionKey = 'qanda.ecmt-short-term.restricted-countries.question.ecmt-short-term-2020-apsg-without-sectors';
            $questionSummaryKey = 'qanda.ecmt-short-term.restricted-countries.question-summary.ecmt-short-term-2020-apsg-without-sectors';
        }

        $guidanceKey = sprintf('qanda.%s.restricted-countries.guidance', $translationKeyFragment);

        $questionText = $this->questionTextGenerator->generate($qaContext);
        $questionText->getQuestion()->getTranslateableText()->setKey($questionKey);
        $questionText->getQuestionSummary()->getTranslateableText()->setKey($questionSummaryKey);
        $questionText->getGuidance()->getTranslateableText()->setKey($guidanceKey);

        return $questionText;
    }
}

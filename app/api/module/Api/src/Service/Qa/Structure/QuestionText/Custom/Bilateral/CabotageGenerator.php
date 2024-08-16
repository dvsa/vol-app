<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class CabotageGenerator implements QuestionTextGeneratorInterface
{
    use IrhpPermitApplicationOnlyTrait;

    /**
     * Create service instance
     *
     *
     * @return CabotageGenerator
     */
    public function __construct(private QuestionTextGenerator $questionTextGenerator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(QaContext $qaContext)
    {
        $irhpPermitApplicationEntity = $qaContext->getQaEntity();

        $countryCode = $irhpPermitApplicationEntity->getIrhpPermitWindow()
            ->getIrhpPermitStock()
            ->getCountry()
            ->getId();

        $questionText = $this->questionTextGenerator->generate($qaContext);
        $additionalGuidanceTranslateableText = $questionText->getAdditionalGuidance()->getTranslateableText();

        $key = sprintf(
            $additionalGuidanceTranslateableText->getKey(),
            strtolower((string) $countryCode)
        );

        $additionalGuidanceTranslateableText->setKey($key);

        return $questionText;
    }
}

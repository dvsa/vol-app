<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio;

use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AlwaysIncludeSlugTrait;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswerSummaryProviderInterface;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionListGenerator;
use Dvsa\Olcs\Api\Service\Qa\Supports\AnyTrait;
use RuntimeException;

class RadioAnswerSummaryProvider implements AnswerSummaryProviderInterface
{
    use AlwaysIncludeSlugTrait;
    use AnyTrait;

    /**
     * Create service instance
     *
     *
     * @return RadioAnswerSummaryProvider
     */
    public function __construct(private OptionListGenerator $optionListGenerator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return 'generic';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateVariables(QaContext $qaContext, ElementInterface $element, $isSnapshot)
    {
        $options = $qaContext->getApplicationStepEntity()->getDecodedOptionSource();

        $radioOptions = $this->optionListGenerator->generate($options['source'])->getOptions();
        $answerValue = $qaContext->getAnswerValue();

        foreach ($radioOptions as $radioOption) {
            if ($radioOption->getValue() == $answerValue) {
                $answerLabel = $radioOption->getLabel();
                return ['answer' => $answerLabel];
            }
        }

        throw new RuntimeException('Answer not found in list of options');
    }
}

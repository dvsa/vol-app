<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Checkbox;

use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AlwaysIncludeSlugTrait;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswerSummaryProviderInterface;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\AnyTrait;

class CheckboxAnswerSummaryProvider implements AnswerSummaryProviderInterface
{
    use AlwaysIncludeSlugTrait;
    use AnyTrait;

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
        $representation = $element->getRepresentation();
        $labelKey = $representation[Checkbox::LABEL_KEY]['key'];

        return ['answer' => $labelKey];
    }
}

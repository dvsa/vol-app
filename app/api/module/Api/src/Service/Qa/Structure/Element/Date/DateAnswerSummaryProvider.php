<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date;

use DateTime;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AlwaysIncludeSlugTrait;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswerSummaryProviderInterface;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\AnyTrait;

class DateAnswerSummaryProvider implements AnswerSummaryProviderInterface
{
    use AlwaysIncludeSlugTrait;
    use AnyTrait;

    public const DATE_FORMAT = 'd/m/Y';

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
        $dateTime = new DateTime(
            $qaContext->getAnswerValue()
        );

        return [
            'answer' => $dateTime->format(self::DATE_FORMAT)
        ];
    }
}

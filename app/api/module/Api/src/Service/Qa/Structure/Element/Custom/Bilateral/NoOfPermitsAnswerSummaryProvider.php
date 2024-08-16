<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AlwaysIncludeSlugTrait;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswerSummaryProviderInterface;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class NoOfPermitsAnswerSummaryProvider implements AnswerSummaryProviderInterface
{
    use AlwaysIncludeSlugTrait;
    use IrhpPermitApplicationOnlyTrait;

    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return 'bilateral-permits-required';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateVariables(QaContext $qaContext, ElementInterface $element, $isSnapshot)
    {
        $irhpPermitApplication = $qaContext->getQaEntity();

        $usage = $irhpPermitApplication->getBilateralPermitUsageSelection();

        $rows = [];

        foreach ($irhpPermitApplication->getFilteredBilateralRequired() as $type => $count) {
            $rows[] = [
                'key' => sprintf(
                    'qanda.bilateral.no-of-permits.%s.%s',
                    $usage,
                    $type
                ),
                'count' => $count
            ];
        }

        return [
            'rows' => $rows
        ];
    }
}

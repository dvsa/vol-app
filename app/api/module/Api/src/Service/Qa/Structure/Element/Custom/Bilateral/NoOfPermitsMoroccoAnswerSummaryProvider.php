<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AlwaysIncludeSlugTrait;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswerSummaryProviderInterface;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class NoOfPermitsMoroccoAnswerSummaryProvider implements AnswerSummaryProviderInterface
{
    use AlwaysIncludeSlugTrait;
    use IrhpPermitApplicationOnlyTrait;

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
        $irhpPermitApplication = $qaContext->getQaEntity();

        $bilateralRequired = $irhpPermitApplication->getFilteredBilateralRequired();
        $moroccoBilateralRequired = $bilateralRequired[IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED];

        return ['answer' => $moroccoBilateralRequired];
    }
}

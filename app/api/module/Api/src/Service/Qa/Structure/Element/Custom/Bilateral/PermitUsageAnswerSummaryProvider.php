<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswerSummaryProviderInterface;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Supports\IrhpPermitApplicationOnlyTrait;

class PermitUsageAnswerSummaryProvider implements AnswerSummaryProviderInterface
{
    use IrhpPermitApplicationOnlyTrait;

    /**
     * {@inheritdoc}
     */
    public function shouldIncludeSlug(QaEntityInterface $qaEntity)
    {
        /** @var IrhpPermitApplication $qaEntity */
        $permitUsageList = $qaEntity->getIrhpPermitWindow()
            ->getIrhpPermitStock()
            ->getPermitUsageList();

        return count($permitUsageList) > 1;
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
        return ['answer' => $qaContext->getAnswerValue()];
    }
}

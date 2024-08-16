<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;

interface AnswersSummaryRowsAdderInterface
{
    /**
     * Add one or more AnswersSummaryRow instances to the provided AnswersSummary instance
     *
     * @param QaEntityInterface $qaEntityInterface
     * @param bool $isSnapshot
     */
    public function addRows(AnswersSummary $answersSummary, QaEntityInterface $qaEntity, $isSnapshot);
}

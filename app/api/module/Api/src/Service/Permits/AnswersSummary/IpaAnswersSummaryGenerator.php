<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;

class IpaAnswersSummaryGenerator
{
    /** @var array */
    private $customRowsAdders = [];

    /**
     * Create service instance
     *
     *
     * @return AnswersSummaryGenerator
     */
    public function __construct(private readonly AnswersSummaryFactory $answersSummaryFactory, private readonly AnswersSummaryRowsAdderInterface $defaultAnswersSummaryRowsAdder)
    {
    }

    /**
     * Build and return a AnswersSummary instance using the appropriate data sources
     *
     * @param bool $isSnapshot
     * @return AnswersSummary
     */
    public function generate(IrhpPermitApplicationEntity $irhpPermitApplicationEntity, $isSnapshot = false)
    {
        $answersSummary = $this->answersSummaryFactory->create();

        $irhpPermitTypeId = $irhpPermitApplicationEntity->getIrhpApplication()->getIrhpPermitType()->getId();
        $rowsAdder = $this->defaultAnswersSummaryRowsAdder;

        if (isset($this->customRowsAdders[$irhpPermitTypeId])) {
            $rowsAdder = $this->customRowsAdders[$irhpPermitTypeId];
        }

        $rowsAdder->addRows($answersSummary, $irhpPermitApplicationEntity, $isSnapshot);

        return $answersSummary;
    }

    /**
     * Register a custom rows adder to accommodate old non-q&a types
     *
     * @param int $irhpPermitTypeId
     */
    public function registerCustomRowsAdder($irhpPermitTypeId, AnswersSummaryRowsAdderInterface $rowsAdder)
    {
        $this->customRowsAdders[$irhpPermitTypeId] = $rowsAdder;
    }
}

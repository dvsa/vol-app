<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;

class AnswersSummaryGenerator
{
    /** @var array */
    private $customRowsAdders = [];

    /**
     * Create service instance
     *
     *
     * @return AnswersSummaryGenerator
     */
    public function __construct(private readonly AnswersSummaryFactory $answersSummaryFactory, private readonly AnswersSummaryRowsAdderInterface $headerAnswersSummaryRowsAdder, private readonly AnswersSummaryRowsAdderInterface $defaultAnswersSummaryRowsAdder)
    {
    }

    /**
     * Build and return a AnswersSummary instance using the appropriate data sources
     *
     * @param bool $isSnapshot
     * @return AnswersSummary
     */
    public function generate(IrhpApplicationEntity $irhpApplicationEntity, $isSnapshot = false)
    {
        $answersSummary = $this->answersSummaryFactory->create();
        $this->headerAnswersSummaryRowsAdder->addRows($answersSummary, $irhpApplicationEntity, $isSnapshot);

        $irhpPermitTypeId = $irhpApplicationEntity->getIrhpPermitType()->getId();
        $rowsAdder = $this->defaultAnswersSummaryRowsAdder;
        if (isset($this->customRowsAdders[$irhpPermitTypeId])) {
            $rowsAdder = $this->customRowsAdders[$irhpPermitTypeId];
        }

        $rowsAdder->addRows($answersSummary, $irhpApplicationEntity, $isSnapshot);

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

<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

use DateTime;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Laminas\View\Renderer\RendererInterface;

class BilateralIpaAnswersSummaryRowsAdder implements AnswersSummaryRowsAdderInterface
{
    public const TEMPLATE_DIRECTORY = 'answers-summary/';

    /**
     * Create service instance
     *
     *
     * @return BilateralIpaAnswersSummaryRowsAdder
     */
    public function __construct(private readonly AnswersSummaryRowFactory $answersSummaryRowFactory, private readonly RendererInterface $viewRenderer, private readonly AnswersSummaryRowsAdderInterface $qaAnswersSummaryRowsAdder, private readonly IrhpPermitStockRepository $irhpPermitStockRepo)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addRows(AnswersSummary $answersSummary, QaEntityInterface $irhpPermitApplication, $isSnapshot)
    {
        $answersSummary->addRow(
            $this->getPeriodRequiredRow($irhpPermitApplication, $isSnapshot)
        );

        $this->qaAnswersSummaryRowsAdder->addRows($answersSummary, $irhpPermitApplication, $isSnapshot);
    }

    /**
     * Get a row representing the period required for a bilateral application
     *
     * @param bool $isSnapshot
     * @return AnswersSummaryRow
     */
    private function getPeriodRequiredRow(IrhpPermitApplicationEntity $irhpPermitApplication, $isSnapshot)
    {
        $stock = $irhpPermitApplication->getIrhpPermitWindow()
            ->getIrhpPermitStock();

        $templateVariables = [
            'answer' => $stock->getPeriodNameKey()
        ];

        $formattedAnswer = $this->viewRenderer->render(
            self::TEMPLATE_DIRECTORY . 'generic',
            $templateVariables
        );

        $availableStocks = $this->irhpPermitStockRepo->fetchOpenBilateralStocksByCountry(
            $stock->getCountry()->getId(),
            new DateTime()
        );

        $isMultiStock = count($availableStocks) > 1;

        $slug = null;

        if (!$isSnapshot && $isMultiStock) {
            $slug = 'period';
        }

        return $this->answersSummaryRowFactory->create(
            $stock->getBilateralAnswerSummaryLabelKey($isMultiStock),
            $formattedAnswer,
            $slug
        );
    }
}

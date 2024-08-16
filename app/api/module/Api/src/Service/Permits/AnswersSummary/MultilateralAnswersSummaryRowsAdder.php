<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Laminas\View\Renderer\RendererInterface;

class MultilateralAnswersSummaryRowsAdder implements AnswersSummaryRowsAdderInterface
{
    public const TEMPLATE_DIRECTORY = 'answers-summary/';

    /**
     * Create service instance
     *
     *
     * @return MultilateralAnswersSummaryRowsAdder
     */
    public function __construct(private readonly AnswersSummaryRowFactory $answersSummaryRowFactory, private readonly RendererInterface $viewRenderer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addRows(AnswersSummary $answersSummary, QaEntityInterface $irhpApplication, $isSnapshot)
    {
        $irhpPermitApplications = $irhpApplication->getIrhpPermitApplications();

        foreach ($irhpPermitApplications as $irhpPermitApplication) {
            $validityYear = $irhpPermitApplication->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getValidityYear();

            $rows[] = [
                'permitsRequired' => $irhpPermitApplication->countPermitsRequired(),
                'year' => $validityYear
            ];
        }

        $formattedAnswer = $this->viewRenderer->render(
            self::TEMPLATE_DIRECTORY . 'multilateral-permits-required',
            ['rows' => $rows]
        );

        $answersSummaryRow = $this->answersSummaryRowFactory->create(
            'permits.irhp.application.question.no-of-permits.question-summary',
            $formattedAnswer,
            'no-of-permits'
        );

        $answersSummary->addRow($answersSummaryRow);
    }
}

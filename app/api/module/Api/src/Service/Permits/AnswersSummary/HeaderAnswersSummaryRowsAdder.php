<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Laminas\View\Renderer\RendererInterface;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;

class HeaderAnswersSummaryRowsAdder implements AnswersSummaryRowsAdderInterface
{
    public const TEMPLATE_DIRECTORY = 'answers-summary/';

    /**
     * Create service instance
     *
     *
     * @return HeaderAnswersSummaryRowsAdder
     */
    public function __construct(private readonly AnswersSummaryRowFactory $answersSummaryRowFactory, private readonly RendererInterface $viewRenderer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addRows(AnswersSummary $answersSummary, QaEntityInterface $entity, $isSnapshot)
    {
        if (!$isSnapshot) {
            $answersSummary->addRow(
                $this->generatePermitTypeRow($entity)
            );
        }

        $answersSummary->addRow(
            $this->generateLicenceRow($entity)
        );
    }

    /**
     * Return a row representing the selected permit type
     *
     *
     * @return AnswersSummaryRow
     */
    private function generatePermitTypeRow(IrhpApplicationEntity $irhpApplicationEntity)
    {
        $answer = $irhpApplicationEntity->getIrhpPermitType()->getName()->getDescription();

        $formattedAnswer = $this->viewRenderer->render(
            self::TEMPLATE_DIRECTORY . 'generic',
            ['answer' => $answer]
        );

        return $this->answersSummaryRowFactory->create(
            'permits.page.fee.permit.type',
            $formattedAnswer
        );
    }

    /**
     * Return a row representing the selected licence
     *
     *
     * @return AnswersSummaryRow
     */
    private function generateLicenceRow(IrhpApplicationEntity $irhpApplicationEntity)
    {
        $licence = $irhpApplicationEntity->getLicence();

        $formattedAnswer = $this->viewRenderer->render(
            self::TEMPLATE_DIRECTORY . 'licence',
            [
                'licenceNo' => $licence->getLicNo(),
                'trafficAreaName' => $licence->getTrafficArea()->getName()
            ]
        );

        return $this->answersSummaryRowFactory->create(
            'permits.check-answers.page.question.licence',
            $formattedAnswer
        );
    }
}

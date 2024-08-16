<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Laminas\View\Renderer\RendererInterface;

class BilateralAnswersSummaryRowsAdder implements AnswersSummaryRowsAdderInterface
{
    public const TEMPLATE_DIRECTORY = 'answers-summary/';

    /**
     * Create service instance
     *
     *
     * @return BilateralAnswersSummaryRowsAdder
     */
    public function __construct(private readonly AnswersSummaryRowFactory $answersSummaryRowFactory, private readonly RendererInterface $viewRenderer, private readonly BilateralIpaAnswersSummaryRowsAdder $bilateralIpaAnswersSummaryRowsAdder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addRows(AnswersSummary $answersSummary, QaEntityInterface $irhpApplication, $isSnapshot)
    {
        $irhpPermitApplications = $irhpApplication->getIrhpPermitApplicationsByCountryName();

        $answersSummary->addRow(
            $this->getCountryNamesRow($irhpPermitApplications, $isSnapshot)
        );

        foreach ($irhpPermitApplications as $irhpPermitApplication) {
            $answersSummary->addRow(
                $this->getCountryNameRow($irhpPermitApplication)
            );

            $this->bilateralIpaAnswersSummaryRowsAdder->addRows($answersSummary, $irhpPermitApplication, $isSnapshot);
        }
    }

    /**
     * Get a row representing the country names for a bilateral application
     *
     * @param $isSnapshot
     * @return AnswersSummaryRow
     */
    private function getCountryNamesRow(mixed $irhpPermitApplications, $isSnapshot)
    {
        $countryNames = [];

        foreach ($irhpPermitApplications as $irhpPermitApplication) {
            $countryNames[] = $irhpPermitApplication->getIrhpPermitWindow()
                ->getIrhpPermitStock()
                ->getCountry()
                ->getCountryDesc();
        }

        $templateVariables = [
            'countryNames' => array_values(array_unique($countryNames))
        ];

        $formattedAnswer = $this->viewRenderer->render(
            self::TEMPLATE_DIRECTORY . 'bilateral-country-names',
            $templateVariables
        );

        return $this->answersSummaryRowFactory->create(
            $isSnapshot
                ? 'permits.irhp.application.question.countries-snapshot'
                : 'permits.irhp.application.question.countries',
            $formattedAnswer
        );
    }

    /**
     * Get a row representing the country name for a bilateral application
     *
     *
     * @return AnswersSummaryRow
     */
    private function getCountryNameRow(IrhpPermitApplication $irhpPermitApplication)
    {
        $countryName = $irhpPermitApplication->getIrhpPermitWindow()
            ->getIrhpPermitStock()
            ->getCountry()
            ->getCountryDesc();

        $templateVariables = [
            'answer' => $countryName
        ];

        $formattedAnswer = $this->viewRenderer->render(
            self::TEMPLATE_DIRECTORY . 'generic',
            $templateVariables
        );

        return $this->answersSummaryRowFactory->create(
            'permits.irhp.application.question.country',
            $formattedAnswer
        );
    }
}

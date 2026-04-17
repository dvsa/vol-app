<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\AnswersSummary;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as IrhpPermitStockEntity;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummary;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRow;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRowFactory;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\BilateralAnswersSummaryRowsAdder;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\BilateralIpaAnswersSummaryRowsAdder;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * BilateralAnswersSummaryRowsAdderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class BilateralAnswersSummaryRowsAdderTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testAddRows(mixed $isSnapshot, mixed $countriesQuestionKey): void
    {
        $countryNamesFormattedAnswer = 'country names line 1<br>country names line 2';
        $countryNamesAnswersSummaryRow = m::mock(AnswersSummaryRow::class);
        $country1AnswersSummaryRow = m::mock(AnswersSummaryRow::class);
        $country2AnswersSummaryRow = m::mock(AnswersSummaryRow::class);
        $country3AnswersSummaryRow = m::mock(AnswersSummaryRow::class);

        $answersSummary = m::mock(AnswersSummary::class);
        $answersSummary->shouldReceive('addRow')
            ->with($countryNamesAnswersSummaryRow)
            ->once()
            ->globally()
            ->ordered();

        $irhpPermitApplication1CountryName = 'Hungary';
        $irhpPermitApplication1CountryNameFormatted = 'Hungary formatted';
        $irhpPermitApplication1 = $this->createMockIrhpPermitApplication(
            $irhpPermitApplication1CountryName
        );

        $irhpPermitApplication2CountryName = 'Spain';
        $irhpPermitApplication2CountryNameFormatted = 'Spain formatted';
        $irhpPermitApplication2 = $this->createMockIrhpPermitApplication(
            $irhpPermitApplication2CountryName
        );

        $irhpPermitApplication3CountryName = 'Spain';
        $irhpPermitApplication3CountryNameFormatted = 'Spain formatted';
        $irhpPermitApplication3 = $this->createMockIrhpPermitApplication(
            $irhpPermitApplication3CountryName
        );

        $irhpPermitApplications = [
            $irhpPermitApplication1,
            $irhpPermitApplication2,
            $irhpPermitApplication3
        ];

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('getIrhpPermitApplicationsByCountryName')
            ->withNoArgs()
            ->andReturn(new ArrayCollection($irhpPermitApplications));

        $answersSummaryRowFactory = m::mock(AnswersSummaryRowFactory::class);
        $answersSummaryRowFactory->shouldReceive('create')
            ->with($countriesQuestionKey, $countryNamesFormattedAnswer)
            ->once()
            ->andReturn($countryNamesAnswersSummaryRow)
            ->shouldReceive('create')
            ->with('permits.irhp.application.question.country', $irhpPermitApplication1CountryNameFormatted)
            ->once()
            ->andReturn($country1AnswersSummaryRow)
            ->shouldReceive('create')
            ->with('permits.irhp.application.question.country', $irhpPermitApplication2CountryNameFormatted)
            ->once()
            ->andReturn($country2AnswersSummaryRow)
            ->shouldReceive('create')
            ->with('permits.irhp.application.question.country', $irhpPermitApplication3CountryNameFormatted)
            ->once()
            ->andReturn($country3AnswersSummaryRow);

        $expectedCountryNamesTemplateVariables = [
            'countryNames' => [
                $irhpPermitApplication1CountryName,
                $irhpPermitApplication2CountryName
            ]
        ];

        $viewRenderer = m::mock(RendererInterface::class);
        $viewRenderer->shouldReceive('render')
            ->with('answers-summary/bilateral-country-names', $expectedCountryNamesTemplateVariables)
            ->once()
            ->andReturn($countryNamesFormattedAnswer)
            ->shouldReceive('render')
            ->with('answers-summary/generic', ['answer' => $irhpPermitApplication1CountryName])
            ->once()
            ->andReturn($irhpPermitApplication1CountryNameFormatted)
            ->shouldReceive('render')
            ->with('answers-summary/generic', ['answer' => $irhpPermitApplication2CountryName])
            ->once()
            ->andReturn($irhpPermitApplication2CountryNameFormatted)
            ->shouldReceive('render')
            ->with('answers-summary/generic', ['answer' => $irhpPermitApplication3CountryName])
            ->once()
            ->andReturn($irhpPermitApplication3CountryNameFormatted);

        $bilateralIpaAnswersSummaryRowsAdder = m::mock(BilateralIpaAnswersSummaryRowsAdder::class);

        $answersSummary->shouldReceive('addRow')
            ->with($country1AnswersSummaryRow)
            ->once()
            ->globally()
            ->ordered();
        $bilateralIpaAnswersSummaryRowsAdder->shouldReceive('addRows')
            ->with($answersSummary, $irhpPermitApplication1, $isSnapshot)
            ->once()
            ->globally()
            ->ordered();

        $answersSummary->shouldReceive('addRow')
            ->with($country2AnswersSummaryRow)
            ->once()
            ->globally()
            ->ordered();
        $bilateralIpaAnswersSummaryRowsAdder->shouldReceive('addRows')
            ->with($answersSummary, $irhpPermitApplication2, $isSnapshot)
            ->once()
            ->globally()
            ->ordered();

        $answersSummary->shouldReceive('addRow')
            ->with($country3AnswersSummaryRow)
            ->once()
            ->globally()
            ->ordered();
        $bilateralIpaAnswersSummaryRowsAdder->shouldReceive('addRows')
            ->with($answersSummary, $irhpPermitApplication3, $isSnapshot)
            ->once()
            ->globally()
            ->ordered();

        $bilateralAnswersSummaryRowsAdder = new BilateralAnswersSummaryRowsAdder(
            $answersSummaryRowFactory,
            $viewRenderer,
            $bilateralIpaAnswersSummaryRowsAdder
        );

        $bilateralAnswersSummaryRowsAdder->addRows($answersSummary, $irhpApplication, $isSnapshot);
    }

    public static function dpSnapshot(): array
    {
        return [
            [true, 'permits.irhp.application.question.countries-snapshot'],
            [false, 'permits.irhp.application.question.countries']
        ];
    }

    private function createMockIrhpPermitApplication(mixed $countryName): mixed
    {
        $irhpPermitStock = m::mock(IrhpPermitStockEntity::class);
        $irhpPermitStock->shouldReceive('getCountry->getCountryDesc')
            ->withNoArgs()
            ->andReturn($countryName);

        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);

        return $irhpPermitApplication;
    }
}

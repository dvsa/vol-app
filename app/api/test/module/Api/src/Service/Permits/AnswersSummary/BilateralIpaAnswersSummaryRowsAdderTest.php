<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\AnswersSummary;

use DateTime;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as IrhpPermitStockEntity;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummary;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRow;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRowFactory;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRowsAdderInterface;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\BilateralIpaAnswersSummaryRowsAdder;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * BilateralIpaAnswersSummaryRowsAdderTest
 */
class BilateralIpaAnswersSummaryRowsAdderTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpAddRows')]
    public function testAddRows(mixed $isSnapshot, mixed $availableStocks, mixed $isMultiStock, mixed $expectedSlug): void
    {
        $countryId = 'NO';
        $periodNameKey = 'period-key';
        $periodFormattedAnswer = 'formatted period';
        $answerSummaryLabelKey = 'answer.summary.label.key';
        $periodAnswersSummaryRow = m::mock(AnswersSummaryRow::class);

        $answersSummary = m::mock(AnswersSummary::class);
        $answersSummary->shouldReceive('addRow')
            ->with($periodAnswersSummaryRow)
            ->once();

        $irhpPermitStock = m::mock(IrhpPermitStockEntity::class);
        $irhpPermitStock->shouldReceive('getPeriodNameKey')
            ->withNoArgs()
            ->andReturn($periodNameKey)
            ->shouldReceive('getCountry->getId')
            ->withNoArgs()
            ->andReturn($countryId);
        $irhpPermitStock->shouldReceive('getBilateralAnswerSummaryLabelKey')
            ->with($isMultiStock)
            ->andReturn($answerSummaryLabelKey);

        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);

        $answersSummaryRowFactory = m::mock(AnswersSummaryRowFactory::class);
        $answersSummaryRowFactory->shouldReceive('create')
            ->with($answerSummaryLabelKey, $periodFormattedAnswer, $expectedSlug)
            ->once()
            ->andReturn($periodAnswersSummaryRow);

        $expectedPeriodTemplateVariables = [
            'answer' => $periodNameKey,
        ];

        $viewRenderer = m::mock(RendererInterface::class);
        $viewRenderer->shouldReceive('render')
            ->with('answers-summary/generic', $expectedPeriodTemplateVariables)
            ->once()
            ->andReturn($periodFormattedAnswer);

        $answersSummaryRowsAdderInterface = m::mock(AnswersSummaryRowsAdderInterface::class);
        $answersSummaryRowsAdderInterface->shouldReceive('addRows')
            ->with($answersSummary, $irhpPermitApplication, $isSnapshot)
            ->once();

        $irhpPermitStockRepository = m::mock(IrhpPermitStockRepository::class);
        $irhpPermitStockRepository->shouldReceive('fetchOpenBilateralStocksByCountry')
            ->with($countryId, m::type(DateTime::class))
            ->once()
            ->andReturn($availableStocks);

        $sut = new BilateralIpaAnswersSummaryRowsAdder(
            $answersSummaryRowFactory,
            $viewRenderer,
            $answersSummaryRowsAdderInterface,
            $irhpPermitStockRepository
        );

        $sut->addRows($answersSummary, $irhpPermitApplication, $isSnapshot);
    }

    public static function dpAddRows(): array
    {
        $noStocks = [];
        $oneStock = [['id' => 1]];
        $multipleStocks = [['id' => 1], ['id' => 2]];

        return [
            [true, $noStocks, false, null],
            [true, $oneStock, false,  null],
            [true, $multipleStocks, true, null],
            [false, $noStocks, false, null],
            [false, $oneStock, false, null],
            [false, $multipleStocks, true, 'period'],
        ];
    }
}

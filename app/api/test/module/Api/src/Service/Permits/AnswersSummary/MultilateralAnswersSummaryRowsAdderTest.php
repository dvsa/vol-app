<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummary;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRow;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRowFactory;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\MultilateralAnswersSummaryRowsAdder;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * MultilateralAnswersSummaryRowsAdderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MultilateralAnswersSummaryRowsAdderTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testAddRows(mixed $isSnapshot): void
    {
        $formattedAnswer = 'line 1<br>line 2';

        $answersSummaryRow = m::mock(AnswersSummaryRow::class);

        $answersSummary = m::mock(AnswersSummary::class);
        $answersSummary->shouldReceive('addRow')
            ->with($answersSummaryRow)
            ->once();

        $irhpPermitApplication1ValidityYear = 2019;
        $irhpPermitApplication1PermitsRequired = 8;
        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getValidityYear')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication1ValidityYear);
        $irhpPermitApplication1->shouldReceive('countPermitsRequired')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication1PermitsRequired);

        $irhpPermitApplication2ValidityYear = 2020;
        $irhpPermitApplication2PermitsRequired = 4;
        $irhpPermitApplication2 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getValidityYear')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication2ValidityYear);
        $irhpPermitApplication2->shouldReceive('countPermitsRequired')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication2PermitsRequired);

        $irhpPermitApplications = [
            $irhpPermitApplication1,
            $irhpPermitApplication2
        ];

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('getIrhpPermitApplications')
            ->withNoArgs()
            ->andReturn($irhpPermitApplications);

        $answersSummaryRowFactory = m::mock(AnswersSummaryRowFactory::class);
        $answersSummaryRowFactory->shouldReceive('create')
            ->with(
                'permits.irhp.application.question.no-of-permits.question-summary',
                $formattedAnswer,
                'no-of-permits'
            )
            ->once()
            ->andReturn($answersSummaryRow);

        $expectedTemplateVariables = [
            'rows' => [
                [
                    'permitsRequired' => 8,
                    'year' => 2019
                ],
                [
                    'permitsRequired' => 4,
                    'year' => 2020
                ]
            ]
        ];

        $viewRenderer = m::mock(RendererInterface::class);
        $viewRenderer->shouldReceive('render')
            ->with('answers-summary/multilateral-permits-required', $expectedTemplateVariables)
            ->once()
            ->andReturn($formattedAnswer);

        $multilateralAnswersSummaryRowsAdder = new MultilateralAnswersSummaryRowsAdder(
            $answersSummaryRowFactory,
            $viewRenderer
        );

        $multilateralAnswersSummaryRowsAdder->addRows($answersSummary, $irhpApplication, $isSnapshot);
    }

    public static function dpSnapshot(): array
    {
        return [
            [true],
            [false]
        ];
    }
}

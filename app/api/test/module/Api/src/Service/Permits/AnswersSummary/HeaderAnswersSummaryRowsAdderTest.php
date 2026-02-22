<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\AnswersSummary;

use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummary;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRow;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryRowFactory;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\HeaderAnswersSummaryRowsAdder;
use Laminas\View\Renderer\RendererInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * HeaderAnswersSummaryRowsAdderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class HeaderAnswersSummaryRowsAdderTest extends MockeryTestCase
{
    public const LIC_NO = 'AB1234567';

    public const TRAFFIC_AREA_NAME = 'Wales';

    private $licence;

    private $irhpApplication;

    private $answersSummaryRowFactory;

    private $viewRenderer;

    private $headerAnswersSummaryRowsAdder;

    public function setUp(): void
    {
        $this->licence = m::mock(LicenceEntity::class);
        $this->licence->shouldReceive('getLicNo')
            ->withNoArgs()
            ->andReturn(self::LIC_NO);
        $this->licence->shouldReceive('getTrafficArea->getName')
            ->withNoArgs()
            ->andReturn(self::TRAFFIC_AREA_NAME);

        $this->irhpApplication = m::mock(IrhpApplicationEntity::class);
        $this->irhpApplication->shouldReceive('getLicence')
            ->andReturn($this->licence);

        $this->answersSummaryRowFactory = m::mock(AnswersSummaryRowFactory::class);

        $this->viewRenderer = m::mock(RendererInterface::class);

        $this->headerAnswersSummaryRowsAdder = new HeaderAnswersSummaryRowsAdder(
            $this->answersSummaryRowFactory,
            $this->viewRenderer
        );
    }

    public function testAddRowsIsSnapshot(): void
    {
        $formattedAnswer = 'licenceNo<br>trafficAreaNo';

        $expectedTemplateVariables = [
            'licenceNo' => self::LIC_NO,
            'trafficAreaName' => self::TRAFFIC_AREA_NAME
        ];

        $this->viewRenderer->shouldReceive('render')
            ->with('answers-summary/licence', $expectedTemplateVariables)
            ->once()
            ->andReturn($formattedAnswer);

        $answersSummaryRow = m::mock(AnswersSummaryRow::class);

        $this->answersSummaryRowFactory->shouldReceive('create')
            ->with('permits.check-answers.page.question.licence', $formattedAnswer)
            ->once()
            ->andReturn($answersSummaryRow);

        $answersSummary = m::mock(AnswersSummary::class);
        $answersSummary->shouldReceive('addRow')
            ->once()
            ->with($answersSummaryRow);

        $this->headerAnswersSummaryRowsAdder->addRows($answersSummary, $this->irhpApplication, true);
    }

    public function testAddRowsIsNotSnapshot(): void
    {
        $permitTypeDescription = 'Bilateral Permit';

        $permitTypeFormattedAnswer = '<strong>Bilateral Permit</strong>';
        $licenceFormattedAnswer = 'licenceNo<br>trafficAreaNo';

        $expectedPermitTypeTemplateVariables = [
            'answer' => $permitTypeDescription
        ];

        $expectedLicenceTemplateVariables = [
            'licenceNo' => self::LIC_NO,
            'trafficAreaName' => self::TRAFFIC_AREA_NAME
        ];

        $this->irhpApplication->shouldReceive('getIrhpPermitType->getName->getDescription')
            ->withNoArgs()
            ->andReturn($permitTypeDescription);

        $this->viewRenderer->shouldReceive('render')
            ->with('answers-summary/generic', $expectedPermitTypeTemplateVariables)
            ->once()
            ->andReturn($permitTypeFormattedAnswer);
        $this->viewRenderer->shouldReceive('render')
            ->with('answers-summary/licence', $expectedLicenceTemplateVariables)
            ->once()
            ->andReturn($licenceFormattedAnswer);

        $permitTypeAnswersSummaryRow = m::mock(AnswersSummaryRow::class);
        $licenceAnswersSummaryRow = m::mock(AnswersSummaryRow::class);

        $this->answersSummaryRowFactory->shouldReceive('create')
            ->with('permits.page.fee.permit.type', $permitTypeFormattedAnswer)
            ->once()
            ->andReturn($permitTypeAnswersSummaryRow);
        $this->answersSummaryRowFactory->shouldReceive('create')
            ->with('permits.check-answers.page.question.licence', $licenceFormattedAnswer)
            ->once()
            ->andReturn($licenceAnswersSummaryRow);

        $answersSummary = m::mock(AnswersSummary::class);
        $answersSummary->shouldReceive('addRow')
            ->once()
            ->with($permitTypeAnswersSummaryRow)
            ->ordered();
        $answersSummary->shouldReceive('addRow')
            ->once()
            ->with($licenceAnswersSummaryRow)
            ->ordered();

        $this->headerAnswersSummaryRowsAdder->addRows($answersSummary, $this->irhpApplication, false);
    }
}

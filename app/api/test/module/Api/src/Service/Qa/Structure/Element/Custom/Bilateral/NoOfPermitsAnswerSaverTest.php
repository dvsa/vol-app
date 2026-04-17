<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\NoOfPermitsConditionalUpdater;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\NamedAnswerFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsAnswerSaverTest extends MockeryTestCase
{
    private $postData = [
        'key1' => 'value1',
        'key2' => 'value2',
    ];

    private $irhpPermitApplication;

    private $applicationStep;

    private $qaContext;

    private $namedAnswerFetcher;

    private $noOfPermitsConditionalUpdater;

    private $noOfPermitsAnswerSaver;

    public function setUp(): void
    {
        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->applicationStep = m::mock(ApplicationStep::class);

        $this->qaContext = m::mock(QaContext::class);
        $this->qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($this->irhpPermitApplication);
        $this->qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($this->applicationStep);

        $this->namedAnswerFetcher = m::mock(NamedAnswerFetcher::class);

        $this->noOfPermitsConditionalUpdater = m::mock(NoOfPermitsConditionalUpdater::class);

        $this->noOfPermitsAnswerSaver = new NoOfPermitsAnswerSaver(
            $this->namedAnswerFetcher,
            $this->noOfPermitsConditionalUpdater
        );
    }

    public function testSaveStandardOnly(): void
    {
        $standardPermitsRequired = 14;

        $this->irhpPermitApplication->shouldReceive('getBilateralCabotageSelection')
            ->withNoArgs()
            ->andReturn(Answer::BILATERAL_STANDARD_ONLY);

        $this->namedAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, $this->postData, IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED)
            ->andReturn($standardPermitsRequired);

        $expectedUpdatedAnswers = [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => $standardPermitsRequired,
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null,
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null,
        ];

        $this->noOfPermitsConditionalUpdater->shouldReceive('update')
            ->with($this->irhpPermitApplication, $expectedUpdatedAnswers)
            ->once();

        $this->noOfPermitsAnswerSaver->save($this->qaContext, $this->postData);
    }

    public function testSaveCabotageOnly(): void
    {
        $cabotagePermitsRequired = 14;

        $this->irhpPermitApplication->shouldReceive('getBilateralCabotageSelection')
            ->withNoArgs()
            ->andReturn(Answer::BILATERAL_CABOTAGE_ONLY);

        $this->namedAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, $this->postData, IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED)
            ->andReturn($cabotagePermitsRequired);

        $expectedUpdatedAnswers = [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => $cabotagePermitsRequired,
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null,
        ];

        $this->noOfPermitsConditionalUpdater->shouldReceive('update')
            ->with($this->irhpPermitApplication, $expectedUpdatedAnswers)
            ->once();

        $this->noOfPermitsAnswerSaver->save($this->qaContext, $this->postData);
    }

    public function testSaveStandardAndCabotage(): void
    {
        $standardPermitsRequired = 17;
        $cabotagePermitsRequired = 12;

        $this->irhpPermitApplication->shouldReceive('getBilateralCabotageSelection')
            ->withNoArgs()
            ->andReturn(Answer::BILATERAL_STANDARD_AND_CABOTAGE);

        $this->namedAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, $this->postData, IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED)
            ->andReturn($standardPermitsRequired);
        $this->namedAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, $this->postData, IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED)
            ->andReturn($cabotagePermitsRequired);

        $expectedUpdatedAnswers = [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => $standardPermitsRequired,
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => $cabotagePermitsRequired,
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null
        ];

        $this->noOfPermitsConditionalUpdater->shouldReceive('update')
            ->with($this->irhpPermitApplication, $expectedUpdatedAnswers)
            ->once();

        $this->noOfPermitsAnswerSaver->save($this->qaContext, $this->postData);
    }
}

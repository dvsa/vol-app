<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\StandardAndCabotageUpdater;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\StandardAndCabotageAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\NamedAnswerFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StandardAndCabotageAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StandardAndCabotageAnswerSaverTest extends MockeryTestCase
{
    private $postData;

    private $applicationStep;

    private $irhpPermitApplication;

    private $qaContext;

    private $namedAnswerFetcher;

    private $standardAndCabotageUpdater;

    private $standardAndCabotageAnswerSaver;

    public function setUp(): void
    {
        $this->postData = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $this->applicationStep = m::mock(ApplicationStep::class);

        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->qaContext = m::mock(QaContext::class);
        $this->qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($this->applicationStep);
        $this->qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($this->irhpPermitApplication);

        $this->namedAnswerFetcher = m::mock(NamedAnswerFetcher::class);

        $this->standardAndCabotageUpdater = m::mock(StandardAndCabotageUpdater::class);

        $this->standardAndCabotageAnswerSaver = new StandardAndCabotageAnswerSaver(
            $this->namedAnswerFetcher,
            $this->standardAndCabotageUpdater
        );
    }

    public function testSaveNoCabotageRequired(): void
    {
        $this->namedAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, $this->postData, 'qaElement')
            ->andReturn('N');

        $this->standardAndCabotageUpdater->shouldReceive('update')
            ->with($this->qaContext, Answer::BILATERAL_STANDARD_ONLY)
            ->once()
            ->globally()
            ->ordered();

        $this->standardAndCabotageAnswerSaver->save($this->qaContext, $this->postData);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSaveCabotageRequired')]
    public function testSaveCabotageRequired(mixed $answerValue): void
    {
        $this->namedAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, $this->postData, 'qaElement')
            ->andReturn('Y');

        $this->namedAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, $this->postData, 'yesContent')
            ->andReturn($answerValue);

        $this->standardAndCabotageUpdater->shouldReceive('update')
            ->with($this->qaContext, $answerValue)
            ->once()
            ->globally()
            ->ordered();

        $this->standardAndCabotageAnswerSaver->save($this->qaContext, $this->postData);
    }

    public static function dpSaveCabotageRequired(): array
    {
        return [
            [Answer::BILATERAL_STANDARD_AND_CABOTAGE],
            [Answer::BILATERAL_CABOTAGE_ONLY],
        ];
    }
}

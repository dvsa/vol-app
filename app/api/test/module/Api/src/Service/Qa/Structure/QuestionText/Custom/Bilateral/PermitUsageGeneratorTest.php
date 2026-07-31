<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\QuestionText\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\Custom\Bilateral\PermitUsageGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionText;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PermitUsageGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class PermitUsageGeneratorTest extends MockeryTestCase
{
    private $irhpPermitApplication;

    private $qaContext;

    private $questionText;

    private $permitUsageGenerator;

    #[\Override]
    public function setUp(): void
    {
        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->qaContext = m::mock(QaContext::class);
        $this->qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($this->irhpPermitApplication);

        $this->questionText = m::mock(QuestionText::class);

        $questionTextGenerator = m::mock(QuestionTextGenerator::class);
        $questionTextGenerator->shouldReceive('generate')
            ->with($this->qaContext)
            ->andReturn($this->questionText);

        $this->permitUsageGenerator = new PermitUsageGenerator($questionTextGenerator);
    }

    public function testGenerateOnePermitUsageAvailable(): void
    {
        $journeys = [
            1 => m::mock(RefData::class),
        ];

        $this->irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getPermitUsageList')
            ->withNoArgs()
            ->andReturn($journeys);

        $this->questionText->shouldReceive('getQuestion->getTranslateableText->setKey')
            ->with('qanda.bilaterals.permit-usage.question.single-option')
            ->once();

        $this->assertSame(
            $this->questionText,
            $this->permitUsageGenerator->generate($this->qaContext)
        );
    }

    public function testGenerateTwoPermitUsagesAvalable(): void
    {
        $journeys = [
            1 => m::mock(RefData::class),
            2 => m::mock(RefData::class),
        ];

        $this->irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getPermitUsageList')
            ->withNoArgs()
            ->andReturn($journeys);

        $this->assertSame(
            $this->questionText,
            $this->permitUsageGenerator->generate($this->qaContext)
        );
    }
}

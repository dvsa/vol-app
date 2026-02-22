<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerWriter;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\ClientReturnCodeHandler;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\CountryDeletingAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CountryDeletingAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CountryDeletingAnswerSaverTest extends MockeryTestCase
{
    public const YES_VALUE = 'yes_value';

    public const POST_DATA = [
        'key1' => 'value1',
        'key2' => 'value2',
    ];

    private $applicationStep;

    private $qaContext;

    private $genericAnswerFetcher;

    private $genericAnswerWriter;

    private $clientReturnCodeHandler;

    private $countryDeletingAnswerSaver;

    public function setUp(): void
    {
        $this->applicationStep = m::mock(ApplicationStep::class);

        $this->qaContext = m::mock(QaContext::class);
        $this->qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($this->applicationStep);

        $this->genericAnswerFetcher = m::mock(GenericAnswerFetcher::class);

        $this->genericAnswerWriter = m::mock(GenericAnswerWriter::class);

        $this->clientReturnCodeHandler = m::mock(ClientReturnCodeHandler::class);

        $this->countryDeletingAnswerSaver = new CountryDeletingAnswerSaver(
            $this->genericAnswerFetcher,
            $this->genericAnswerWriter,
            $this->clientReturnCodeHandler
        );
    }

    public function testSaveAnswerIsYes(): void
    {
        $this->genericAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, self::POST_DATA)
            ->andReturn('Y');

        $this->genericAnswerWriter->shouldReceive('write')
            ->with($this->qaContext, self::YES_VALUE)
            ->once();

        $this->assertNull(
            $this->countryDeletingAnswerSaver->save($this->qaContext, self::POST_DATA, self::YES_VALUE)
        );
    }

    public function testSaveAnswerIsNotYes(): void
    {
        $clientReturnCode = 'RETURN_CODE';

        $this->genericAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, self::POST_DATA)
            ->andReturn('N');

        $this->clientReturnCodeHandler->shouldReceive('handle')
            ->with($this->qaContext)
            ->once()
            ->andReturn($clientReturnCode);

        $this->assertEquals(
            $clientReturnCode,
            $this->countryDeletingAnswerSaver->save($this->qaContext, self::POST_DATA, self::YES_VALUE)
        );
    }
}

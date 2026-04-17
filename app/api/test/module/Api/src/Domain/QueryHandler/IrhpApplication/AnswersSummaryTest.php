<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\AnswersSummary;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummary as AnswersSummaryObj;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\AnswersSummaryGenerator;
use Dvsa\Olcs\Api\Service\Permits\AnswersSummary\IpaAnswersSummaryGenerator;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\AnswersSummary as AnswersSummaryQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Laminas\I18n\Translator\Translator;

class AnswersSummaryTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new AnswersSummary();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermitApplication', IrhpPermitApplicationRepo::class);

        $this->mockedSmServices = [
            'PermitsAnswersSummaryGenerator' => m::mock(AnswersSummaryGenerator::class),
            'PermitsIpaAnswersSummaryGenerator' => m::mock(IpaAnswersSummaryGenerator::class),
            'translator' => m::mock(Translator::class)
        ];

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQuery')]
    public function testHandleQuery(mixed $translateToWelsh, mixed $expectedLocale): void
    {
        $previousLocale = 'sq_AL';

        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);
        $irhpApplicationEntity->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnFalse();

        $answersSummaryRepresentation = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $answersSummary = m::mock(AnswersSummaryObj::class);
        $answersSummary->shouldReceive('getRepresentation')
            ->withNoArgs()
            ->andReturn($answersSummaryRepresentation);

        $this->mockedSmServices['PermitsAnswersSummaryGenerator']->shouldReceive('generate')
            ->with($irhpApplicationEntity)
            ->once()
            ->andReturn($answersSummary);

        $this->mockedSmServices['PermitsIpaAnswersSummaryGenerator']->shouldReceive('generate')
            ->never();

        $query = AnswersSummaryQry::create(
            [
                'id' => 56,
                'translateToWelsh' => $translateToWelsh
            ]
        );

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($irhpApplicationEntity);

        $this->repoMap['IrhpPermitApplication']->shouldReceive('fetchById')
            ->never();

        $this->mockedSmServices['translator']->shouldReceive('getLocale')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($previousLocale);

        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->with($expectedLocale)
            ->once()
            ->globally()
            ->ordered();

        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->with($previousLocale)
            ->once()
            ->globally()
            ->ordered();

        $this->assertEquals(
            $answersSummaryRepresentation,
            $this->sut->handleQuery($query)
        );
    }

    public static function dpHandleQuery(): array
    {
        return [
            ['Y', 'cy_GB'],
            ['N', 'en_GB']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQuery')]
    public function testHandleQueryForBilateral(mixed $translateToWelsh, mixed $expectedLocale): void
    {
        $previousLocale = 'sq_AL';
        $irhpPermitApplicationId = 100;

        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);
        $irhpApplicationEntity->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $irhpPermitApplicationEntity = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplicationEntity->shouldReceive('getIrhpApplication')
            ->withNoArgs()
            ->andReturn($irhpApplicationEntity);

        $answersSummaryRepresentation = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $answersSummary = m::mock(AnswersSummaryObj::class);
        $answersSummary->shouldReceive('getRepresentation')
            ->withNoArgs()
            ->andReturn($answersSummaryRepresentation);

        $this->mockedSmServices['PermitsAnswersSummaryGenerator']->shouldReceive('generate')
            ->never();

        $this->mockedSmServices['PermitsIpaAnswersSummaryGenerator']->shouldReceive('generate')
            ->with($irhpPermitApplicationEntity)
            ->once()
            ->andReturn($answersSummary);

        $query = AnswersSummaryQry::create(
            [
                'id' => 56,
                'irhpPermitApplication' => $irhpPermitApplicationId,
                'translateToWelsh' => $translateToWelsh
            ]
        );

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($irhpApplicationEntity);

        $this->repoMap['IrhpPermitApplication']->shouldReceive('fetchById')
            ->with($irhpPermitApplicationId)
            ->andReturn($irhpPermitApplicationEntity);

        $this->mockedSmServices['translator']->shouldReceive('getLocale')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($previousLocale);

        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->with($expectedLocale)
            ->once()
            ->globally()
            ->ordered();

        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->with($previousLocale)
            ->once()
            ->globally()
            ->ordered();

        $this->assertEquals(
            $answersSummaryRepresentation,
            $this->sut->handleQuery($query)
        );
    }

    public function testHandleQueryForBilateralMismatchedIds(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Mismatched IrhpApplication and IrhpPermitApplication');

        $irhpPermitApplicationId = 100;

        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);
        $irhpApplicationEntity->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturnTrue();

        $irhpPermitApplicationEntity = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplicationEntity->shouldReceive('getIrhpApplication')
            ->withNoArgs()
            ->andReturnNull();

        $this->mockedSmServices['PermitsAnswersSummaryGenerator']->shouldReceive('generate')
            ->never();

        $this->mockedSmServices['PermitsIpaAnswersSummaryGenerator']->shouldReceive('generate')
            ->never();

        $query = AnswersSummaryQry::create(
            [
                'id' => 56,
                'irhpPermitApplication' => $irhpPermitApplicationId,
                'translateToWelsh' => 'N'
            ]
        );

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($irhpApplicationEntity);

        $this->repoMap['IrhpPermitApplication']->shouldReceive('fetchById')
            ->with($irhpPermitApplicationId)
            ->andReturn($irhpPermitApplicationEntity);

        $this->mockedSmServices['translator']->shouldReceive('getLocale')
            ->never();

        $this->mockedSmServices['translator']->shouldReceive('setLocale')
            ->never();

        $this->sut->handleQuery($query);
    }
}

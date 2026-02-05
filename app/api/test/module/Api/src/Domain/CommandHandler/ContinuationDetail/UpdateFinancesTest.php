<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ContinuationDetail;

use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail\UpdateFinances as CommandHandler;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\UpdateFinances as UpdateCommand;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;

/**
 * UpdateFinancesTest
 */
class UpdateFinancesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ContinuationDetail', \Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 3,
            'version' => 7,
            'averageBalanceAmount' => '123.45',
            'hasOverdraft' => 'Y',
            'overdraftAmount' => '234.56',
            'hasOtherFinances' => 'Y',
            'otherFinancesAmount' => '345.67',
            'otherFinancesDetails' => 'FOO',
        ];
        $command = UpdateCommand::create($data);

        $continuationDetail = new ContinuationDetailEntity();
        $continuationDetail->setId(154);

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchById')->with(3, Query::HYDRATE_OBJECT, 7)->once()
            ->andReturn($continuationDetail);

        $this->repoMap['ContinuationDetail']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame($data['averageBalanceAmount'], $continuationDetail->getAverageBalanceAmount());
        $this->assertSame($data['hasOverdraft'], $continuationDetail->getHasOverdraft());
        $this->assertSame($data['overdraftAmount'], $continuationDetail->getOverdraftAmount());
        $this->assertSame($data['hasOtherFinances'], $continuationDetail->getHasOtherFinances());
        $this->assertSame($data['otherFinancesAmount'], $continuationDetail->getOtherFinancesAmount());
        $this->assertSame($data['otherFinancesDetails'], $continuationDetail->getOtherFinancesDetails());

        $this->assertEquals(['ContinuationDetail finances updated'], $result->getMessages());
        $this->assertEquals(['continuationDetail' => 154], $result->getIds());
    }

    public function testHandleCommandNo(): void
    {
        $data = [
            'id' => 3,
            'version' => 7,
            'averageBalanceAmount' => '123.45',
            'hasOverdraft' => 'N',
            'overdraftAmount' => '234.56',
            'hasOtherFinances' => 'N',
            'otherFinancesAmount' => '345.67',
            'otherFinancesDetails' => 'FOO',
        ];
        $command = UpdateCommand::create($data);

        $continuationDetail = new ContinuationDetailEntity();
        $continuationDetail->setId(154);

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchById')->with(3, Query::HYDRATE_OBJECT, 7)->once()
            ->andReturn($continuationDetail);

        $this->repoMap['ContinuationDetail']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame($data['averageBalanceAmount'], $continuationDetail->getAverageBalanceAmount());
        $this->assertSame($data['hasOverdraft'], $continuationDetail->getHasOverdraft());
        $this->assertSame(null, $continuationDetail->getOverdraftAmount());
        $this->assertSame($data['hasOtherFinances'], $continuationDetail->getHasOtherFinances());
        $this->assertSame(null, $continuationDetail->getOtherFinancesAmount());
        $this->assertSame(null, $continuationDetail->getOtherFinancesDetails());

        $this->assertEquals(['ContinuationDetail finances updated'], $result->getMessages());
        $this->assertEquals(['continuationDetail' => 154], $result->getIds());
    }

    public function testHandleCommandFactoringYes(): void
    {
        $data = [
            'id' => 3,
            'version' => 7,
            'averageBalanceAmount' => '123.45',
            'hasOverdraft' => 'N',
            'overdraftAmount' => '234.56',
            'hasFactoring' => 'Y',
            'factoringAmount' => '345.67',
        ];
        $command = UpdateCommand::create($data);

        $continuationDetail = new ContinuationDetailEntity();
        $continuationDetail->setId(154);

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchById')->with(3, Query::HYDRATE_OBJECT, 7)->once()
            ->andReturn($continuationDetail);

        $this->repoMap['ContinuationDetail']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame($data['averageBalanceAmount'], $continuationDetail->getAverageBalanceAmount());
        $this->assertSame($data['hasOverdraft'], $continuationDetail->getHasOverdraft());
        $this->assertSame(null, $continuationDetail->getOverdraftAmount());
        $this->assertSame($data['hasFactoring'], $continuationDetail->getHasFactoring());
        $this->assertSame($data['factoringAmount'], $continuationDetail->getFactoringAmount());

        $this->assertEquals(['ContinuationDetail finances updated'], $result->getMessages());
        $this->assertEquals(['continuationDetail' => 154], $result->getIds());
    }

    public function testHandleCommandFactoringNo(): void
    {
        $data = [
            'id' => 3,
            'version' => 7,
            'averageBalanceAmount' => '123.45',
            'hasOverdraft' => 'N',
            'overdraftAmount' => '234.56',
            'hasFactoring' => 'N',
            'factoringAmount' => '345.67',
        ];
        $command = UpdateCommand::create($data);

        $continuationDetail = new ContinuationDetailEntity();
        $continuationDetail->setId(154);

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchById')->with(3, Query::HYDRATE_OBJECT, 7)->once()
            ->andReturn($continuationDetail);

        $this->repoMap['ContinuationDetail']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame($data['averageBalanceAmount'], $continuationDetail->getAverageBalanceAmount());
        $this->assertSame($data['hasOverdraft'], $continuationDetail->getHasOverdraft());
        $this->assertSame(null, $continuationDetail->getOverdraftAmount());
        $this->assertSame($data['hasFactoring'], $continuationDetail->getHasFactoring());
        $this->assertSame(null, $continuationDetail->getFactoringAmount());

        $this->assertEquals(['ContinuationDetail finances updated'], $result->getMessages());
        $this->assertEquals(['continuationDetail' => 154], $result->getIds());
    }
}

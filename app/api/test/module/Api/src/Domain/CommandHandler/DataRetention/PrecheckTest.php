<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DataRetention;

use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\CommandHandler\DataRetention\Precheck;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use PDOStatement;

class PrecheckTest extends AbstractCommandHandlerTestCase
{
    private $mockedConnection;

    public function setUp(): void
    {
        $this->sut = new Precheck();
        $this->mockedConnection = m::mock(\PDO::class);
        $this->mockedSmServices['doctrine.entitymanager.orm_default'] = m::mock(EntityManager::class);
        $this->mockedSmServices['doctrine.entitymanager.orm_default']
            ->shouldReceive('getConnection->getNativeConnection')
            ->andReturn($this->mockedConnection);
        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = m::mock(CommandInterface::class);
        $command->shouldReceive('getLimit')
            ->withNoArgs()
            ->andReturn(10);

        $mockStatement = m::mock(PDOStatement::class);
        $mockStatement->expects('execute')->withNoArgs();

        $this->mockedConnection->shouldReceive('prepare')
            ->with("CALL sp_dr_precheck(10);")
            ->once()
            ->andReturn($mockStatement);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Calling stored procedure sp_dr_precheck(10)',
                'Precheck procedure executed.'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}

<?php

declare(strict_types=1);

/**
 * Delete Statement Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Statement;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Statement\DeleteStatement as DeleteCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Repository\Statement;
use Dvsa\Olcs\Api\Entity\Cases\Statement as StatementEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Statement\DeleteStatement as DeleteCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Delete Statement Test
 */
class DeleteStatementTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var DeleteCommandHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new DeleteCommandHandler();
        $this->mockRepo('Statement', Statement::class);
        $this->mockRepo('Document', Repository\Document::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 111;

        $data = [
            'id' => $id,
            'version' => 2
        ];

        $command = DeleteCommand::create($data);

        /** @var StatementEntity $statementEntity */
        $statementEntity = m::mock(StatementEntity::class)->makePartial();
        $statementEntity->setId($command->getId());

        $this->repoMap['Statement']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($statementEntity)
            ->shouldReceive('delete')
            ->with(m::type(StatementEntity::class))
            ->once();

        $this->repoMap['Document']->shouldReceive('fetchListForStatement')->with($id)->once()->andReturn(
            ['DOCUMENT1', 'DOCUMENT2']
        );
        $this->repoMap['Document']->shouldReceive('delete')->with('DOCUMENT1')->once();
        $this->repoMap['Document']->shouldReceive('delete')->with('DOCUMENT2')->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Statement deleted', $result->getMessages());
    }
}

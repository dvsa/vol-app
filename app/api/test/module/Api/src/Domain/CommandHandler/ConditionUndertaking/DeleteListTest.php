<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\DeleteList as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking\DeleteList::class)]
class DeleteListTest extends AbstractCommandHandlerTestCase
{
    public const CU_ID = 8001;
    public const CU2_ID = 8002;

    /** @var  CommandHandler\ConditionUndertaking\DeleteList */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CommandHandler\ConditionUndertaking\DeleteList();

        $this->mockRepo('ConditionUndertaking', Repository\ConditionUndertaking::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [self::CU_ID, self::CU2_ID],
        ];
        $cntDel = 999;

        $command = Command::create($data);

        $mockCu1 = new Entity\Cases\ConditionUndertaking(new \Dvsa\Olcs\Api\Entity\System\RefData(), 1, 1);
        $mockCu1->setId(self::CU_ID);

        $mockCu2 = new Entity\Cases\ConditionUndertaking(new \Dvsa\Olcs\Api\Entity\System\RefData(), 0, 0);
        $mockCu2->setId(self::CU2_ID);

        $this->repoMap['ConditionUndertaking']
            ->shouldReceive('fetchById')->with(self::CU_ID)->once()->andReturn($mockCu1)
            ->shouldReceive('delete')->with($mockCu1)->once()

            ->shouldReceive('fetchById')->with(self::CU2_ID)->once()->andReturn($mockCu2)
            ->shouldReceive('delete')->with($mockCu2)->once()

            ->shouldReceive('deleteFromVariations')->with($command->getIds())->once()->andReturn($cntDel);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'ConditionUndertaking ID ' . self::CU_ID . ' deleted',
                'ConditionUndertaking ID ' . self::CU2_ID . ' deleted',
                'Deleted from variations ' . $cntDel . ' conditionUndertaking',
            ],
            $result->getMessages()
        );
    }
}

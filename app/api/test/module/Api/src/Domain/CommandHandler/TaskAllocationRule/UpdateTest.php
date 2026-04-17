<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TaskAllocationRule;

use Dvsa\Olcs\Api\Domain\CommandHandler\TaskAllocationRule\Update as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Command\TaskAllocationRule\Update as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class UpdateTest extends AbstractCommandHandlerTestCase
{
    /**
     * Set up
     */
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo(Repository\TaskAllocationRule::class, Repository\TaskAllocationRule::class);

        parent::setUp();
    }

    /**
     * Init references
     */
    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            CategoryEntity::class => [
                1 => m::mock(CategoryEntity::class)
            ],
            TeamEntity::class => [
                2 => m::mock(TeamEntity::class)
            ],
            UserEntity::class => [
                3 => m::mock(UserEntity::class)
            ],
            TrafficAreaEntity::class => [
                'T' => m::mock(UserEntity::class)
            ],
        ];

        $this->refData = ['lcat_gv', 'lcat_psv'];

        parent::initReferences();
    }

    /**
     * Test handle command with all params
     *
     * @param string $goodsOrPsv
     * @param string $mlh
     * @param bool $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mlhProvider')]
    public function testHandleCommandAllParams(mixed $goodsOrPsv, mixed $mlh, mixed $expected): void
    {
        $command = Cmd::create(
            [
                'id' => 1304,
                'version' => 42,
                'category' => 1,
                'team' => 2,
                'user' => 3,
                'goodsOrPsv' => $goodsOrPsv,
                'isMlh' => $mlh,
                'trafficArea' => 'T',
            ]
        );

        $tar = new \Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule();
        $tar->setId(1304);

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchById')
            ->with($command->getId(), \Doctrine\ORM\Query::HYDRATE_OBJECT, 42)->once()->andReturn($tar);

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule $tar) use ($goodsOrPsv, $expected) {
                $this->assertSame($this->references[CategoryEntity::class][1], $tar->getCategory());
                $this->assertSame($this->references[TeamEntity::class][2], $tar->getTeam());
                $this->assertSame($this->references[UserEntity::class][3], $tar->getUser());
                $this->assertSame($this->refData[$goodsOrPsv], $tar->getGoodsOrPsv());
                $this->assertSame($expected, $tar->getIsMlh());
                $this->assertSame($this->references[TrafficAreaEntity::class]['T'], $tar->getTrafficArea());
            }
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'id' => [
                    'task-allocation-rule' => 1304,
                ],
                'messages' => [
                    'TaskAllocationRule updated',
                ]
            ],
            $result->toArray()
        );
    }

    /**
     * MLH provider
     *
     * @return array
     */
    public static function mlhProvider(): array
    {
        return [
            ['lcat_gv', 'Y', true],
            ['lcat_gv', 'N', false],
            ['lcat_psv', 'na', null],
        ];
    }

    /**
     * Test handle command with min params
     */
    public function testHandleCommandMinParams(): void
    {
        $command = Cmd::create(
            [
                'id' => 1304,
                'version' => 42,
                'category' => 1,
                'team' => 2,
            ]
        );

        $tar = new \Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule();
        $tar->setId(1304);

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchById')
            ->with($command->getId(), \Doctrine\ORM\Query::HYDRATE_OBJECT, 42)->once()->andReturn($tar);

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule $tar) {
                $this->assertSame($this->references[CategoryEntity::class][1], $tar->getCategory());
                $this->assertSame($this->references[TeamEntity::class][2], $tar->getTeam());
                $this->assertSame(null, $tar->getUser());
                $this->assertSame(null, $tar->getGoodsOrPsv());
                $this->assertSame(null, $tar->getIsMlh());
                $this->assertSame(null, $tar->getTrafficArea());
                $tar->setId(1304);
            }
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'id' => [
                    'task-allocation-rule' => 1304,
                ],
                'messages' => [
                    'TaskAllocationRule updated',
                ]
            ],
            $result->toArray()
        );
    }
}

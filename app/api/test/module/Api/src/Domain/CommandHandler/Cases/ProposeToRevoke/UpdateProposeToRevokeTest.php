<?php

declare(strict_types=1);

/**
 * Update ProposeToRevoke Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\ProposeToRevoke;

use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\ProposeToRevoke\UpdateProposeToRevoke;
use Dvsa\Olcs\Api\Domain\Repository\ProposeToRevoke;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke as ProposeToRevokeEntity;
use Dvsa\Olcs\Api\Entity\Pi\Reason;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc;
use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\UpdateProposeToRevoke as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Update ProposeToRevoke Test
 */
class UpdateProposeToRevokeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateProposeToRevoke();
        $this->mockRepo('ProposeToRevoke', ProposeToRevoke::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            Reason::class => [
                221 => m::mock(Reason::class)
            ],
            PresidingTc::class => [
                1 => m::mock(PresidingTc::class)
            ],
            User::class => [
                'DUMMY-ASSIGNED-CASEWORKER-ID' => m::mock(User::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'reasons' => [221],
            'presidingTc' => 1,
            'ptrAgreedDate' => '2015-01-01',
            'closedDate' => '2016-01-01',
            'comment' => 'testing',
            'assignedCaseworker' => 'DUMMY-ASSIGNED-CASEWORKER-ID',
        ];

        $command = Cmd::create($data);

        /** @var ProposeToRevokeEntity|m\MockInterface $proposeToRevoke */
        $proposeToRevoke = m::mock(ProposeToRevokeEntity::class)->makePartial();
        $proposeToRevoke->setId($data['id']);

        $this->repoMap['ProposeToRevoke']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($proposeToRevoke);

        $this->repoMap['ProposeToRevoke']->shouldReceive('save')
            ->once()
            ->with(m::type(ProposeToRevokeEntity::class))
            ->andReturnUsing(
                function (ProposeToRevokeEntity $savedProposeToRevoke) use ($data) {
                    $this->assertSame(
                        $this->references[Reason::class][$data['reasons'][0]],
                        $savedProposeToRevoke->getReasons()[0]
                    );
                    $this->assertSame(
                        $this->references[PresidingTc::class][$data['presidingTc']],
                        $savedProposeToRevoke->getPresidingTc()
                    );
                    $this->assertEquals(
                        $data['ptrAgreedDate'],
                        $savedProposeToRevoke->getPtrAgreedDate()->format('Y-m-d')
                    );
                    $this->assertSame(
                        $this->references[User::class]['DUMMY-ASSIGNED-CASEWORKER-ID'],
                        $savedProposeToRevoke->getAssignedCaseworker()
                    );
                    $this->assertEquals($data['closedDate'], $savedProposeToRevoke->getClosedDate()->format('Y-m-d'));
                    $this->assertEquals($data['comment'], $savedProposeToRevoke->getComment());
                }
            );

        $this->expectedSideEffect(
            GenerateSlaTargetDateCmd::class,
            [
                'proposeToRevoke' => $data['id']
            ],
            new Result()
        );
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'proposeToRevoke' => $data['id'],
            ],
            'messages' => [
                'Revocation updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}

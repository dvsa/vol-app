<?php

declare(strict_types=1);

/**
 * Create ProposeToRevoke Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\ProposeToRevoke;

use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\ProposeToRevoke\CreateProposeToRevoke;
use Dvsa\Olcs\Api\Domain\Repository\ProposeToRevoke;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Pi\Reason;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke as ProposeToRevokeEntity;
use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\CreateProposeToRevoke as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Create ProposeToRevoke Test
 */
class CreateProposeToRevokeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateProposeToRevoke();
        $this->mockRepo('ProposeToRevoke', ProposeToRevoke::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            Cases::class => [
                11 => m::mock(Cases::class)
            ],
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
            'case' => 11,
            'reasons' => [221],
            'presidingTc' => 1,
            'ptrAgreedDate' => '2015-01-01',
            'closedDate' => '2016-01-01',
            'comment' => 'testing',
            'assignedCaseworker' => 'DUMMY-ASSIGNED-CASEWORKER-ID',
        ];

        $command = Cmd::create($data);

        $this->repoMap['ProposeToRevoke']->shouldReceive('save')
            ->once()
            ->with(m::type(ProposeToRevokeEntity::class))
            ->andReturnUsing(
                function (ProposeToRevokeEntity $savedProposeToRevoke) use ($data) {
                    $savedProposeToRevoke->setId(111);
                    $this->assertSame(
                        $this->references[Cases::class][$data['case']],
                        $savedProposeToRevoke->getCase()
                    );
                    $this->assertSame(
                        $this->references[Reason::class][$data['reasons'][0]],
                        $savedProposeToRevoke->getReasons()[0]
                    );
                    $this->assertSame(
                        $this->references[PresidingTc::class][$data['presidingTc']],
                        $savedProposeToRevoke->getPresidingTc()
                    );
                    $this->assertSame(
                        $this->references[User::class]['DUMMY-ASSIGNED-CASEWORKER-ID'],
                        $savedProposeToRevoke->getAssignedCaseworker()
                    );
                    $this->assertEquals(
                        $data['ptrAgreedDate'],
                        $savedProposeToRevoke->getPtrAgreedDate()->format('Y-m-d')
                    );
                    $this->assertEquals($data['closedDate'], $savedProposeToRevoke->getClosedDate()->format('Y-m-d'));
                    $this->assertEquals($data['comment'], $savedProposeToRevoke->getComment());
                }
            );

        $this->expectedSideEffect(
            GenerateSlaTargetDateCmd::class,
            [
                'proposeToRevoke' => 111
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'proposeToRevoke' => 111,
            ],
            'messages' => [
                'Revocation created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}

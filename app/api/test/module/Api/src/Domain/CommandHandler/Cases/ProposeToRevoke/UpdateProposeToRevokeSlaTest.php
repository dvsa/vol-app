<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\ProposeToRevoke;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\ProposeToRevoke\UpdateProposeToRevokeSla;
use Dvsa\Olcs\Api\Domain\Repository\ProposeToRevoke;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke as ProposeToRevokeEntity;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc;
use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\UpdateProposeToRevokeSla as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Update ProposeToRevoke Test
 */
class UpdateProposeToRevokeSlaTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateProposeToRevokeSla();
        $this->mockRepo('ProposeToRevoke', ProposeToRevoke::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            PresidingTc::class => [
                4 => m::mock(PresidingTc::class),
                5 => m::mock(PresidingTc::class),
            ],
        ];
        $this->refData = [
            'DUMMY-ACTION',
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 2,
            'version' => 3,
            'isSubmissionRequiredForApproval' => 0,
            'approvalSubmissionIssuedDate' => '2017-01-01',
            'approvalSubmissionReturnedDate' => '2017-01-02',
            'approvalSubmissionPresidingTc' => 4,
            'iorLetterIssuedDate' => '2017-01-03',
            'operatorResponseDueDate' => '2017-01-04',
            'operatorResponseReceivedDate' => '2017-01-05',
            'isSubmissionRequiredForAction' => 1,
            'finalSubmissionIssuedDate' => '2017-01-06',
            'finalSubmissionReturnedDate' => '2017-01-07',
            'finalSubmissionPresidingTc' => 5,
            'actionToBeTaken' => 'DUMMY-ACTION',
            'revocationLetterIssuedDate' => '2017-01-08',
            'nfaLetterIssuedDate' => '2017-01-09',
            'warningLetterIssuedDate' => '2017-01-10',
            'piAgreedDate' => '2017-01-11',
            'otherActionAgreedDate' => '2017-01-12',
        ];

        $command = Cmd::create($data);

        /** @var ProposeToRevokeEntity|m\MockInterface $proposeToRevoke */
        $proposeToRevoke = m::mock(ProposeToRevokeEntity::class)->makePartial();
        $proposeToRevoke->setId(2);

        $this->repoMap['ProposeToRevoke']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 3)
            ->andReturn($proposeToRevoke);

        $this->repoMap['ProposeToRevoke']->shouldReceive('save')
            ->once()
            ->with(m::type(ProposeToRevokeEntity::class))
            ->andReturnSelf();

        $this->expectedSideEffect(
            GenerateSlaTargetDateCmd::class,
            [
                'proposeToRevoke' => $data['id']
            ],
            new Result()
        );
        $result = $this->sut->handleCommand($command);

        foreach ($data as $key => $value) {
            if ($key == 'version') {
                continue;
            }

            $getter = 'get' . ucwords($key);
            $savedValue = $proposeToRevoke->$getter();

            if ($key == 'approvalSubmissionPresidingTc' || $key == 'finalSubmissionPresidingTc') {
                /** @var PresidingTc $presidingTc */
                $presidingTc = $savedValue;
                $savedValue = $presidingTc->getId();
            }
            if ($key == 'actionToBeTaken') {
                $value = $this->refData['DUMMY-ACTION'];
            }

            $this->assertSame($value, $savedValue, $key);
        }

        $expectedResult = [
            'id' => [
                'proposeToRevoke' => $data['id'],
            ],
            'messages' => [
                'Revocation Sla updated successfully'
            ]
        ];

        $this->assertEquals($expectedResult, $result->toArray());
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DigitalSignature;

use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateTmApplication as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\DigitalSignature\UpdateTmApplication as Handler;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerApplication as TmApplicationRepo;
use Dvsa\Olcs\Api\Entity\DigitalSignature as DigitalSignatureEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication as TmApplicationEntity;
use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\Submit as SubmitApplicationCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class UpdateTmApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('TransportManagerApplication', TmApplicationRepo::class);

        $this->refData = [
            RefData::SIG_DIGITAL_SIGNATURE => m::mock(RefData::class),
        ];

        $this->references = [
            DigitalSignatureEntity::class => [
                999 => m::mock(DigitalSignatureEntity::class),
            ],
        ];

        $this->sut = new Handler();

        parent::setUp();
    }

    public function testMissingRole(): void
    {
        $applicationId = 666;
        $digitalSignatureId = 999;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tm Role is not matched');

        $application = m::mock(TmApplicationEntity::class);

        $this->repoMap['TransportManagerApplication']->expects('fetchById')
            ->with($applicationId)
            ->andReturn($application);

        $cmd = Cmd::create(
            ['application' => $applicationId, 'digitalSignature' => $digitalSignatureId, 'role' => 'other role']
        );

        $this->sut->handleCommand($cmd);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRoleProvider')]
    public function testHandleCommand(string $role, string $nextStatus, array $updateMethods): void
    {
        $applicationId = 666;
        $digitalSignatureId = 999;

        $application = m::mock(TmApplicationEntity::class);

        foreach ($updateMethods as $updateMethod) {
            $application->expects($updateMethod)->with(
                $this->refData[RefData::SIG_DIGITAL_SIGNATURE],
                $this->references[DigitalSignatureEntity::class][$digitalSignatureId]
            );
        }

        $this->repoMap['TransportManagerApplication']->expects('fetchById')
            ->with($applicationId)
            ->andReturn($application);

        $this->repoMap['TransportManagerApplication']->expects('save')
            ->with(m::type(TmApplicationEntity::class));

        $this->expectedSideEffect(
            SubmitApplicationCmd::class,
            ['id' => $applicationId, 'nextStatus' => $nextStatus],
            $this->sideEffectResult('submit application message')
        );

        $cmd = Cmd::create(
            ['application' => $applicationId, 'digitalSignature' => $digitalSignatureId, 'role' => $role]
        );

        $result = $this->sut->handleCommand($cmd);
        $this->assertEquals($this->expectedResultMessages(), $result->getMessages());
    }

    public static function dpRoleProvider(): array
    {
        return [
            [RefData::TMA_SIGN_AS_TM, TmApplicationEntity::STATUS_TM_SIGNED, ['updateTmDigitalSignature']],
            [RefData::TMA_SIGN_AS_OP, TmApplicationEntity::STATUS_RECEIVED, ['updateOperatorDigitalSignature']],
            [RefData::TMA_SIGN_AS_TM_OP, TmApplicationEntity::STATUS_RECEIVED, ['updateTmDigitalSignature', 'updateOperatorDigitalSignature']],
        ];
    }

    private function expectedResultMessages(): array
    {
        return [
            0 => 'Digital signature added to TM application 666',
            1 => 'submit application message',
        ];
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DigitalSignature;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateApplication as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\DigitalSignature\UpdateApplication as Handler;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\DigitalSignature as DigitalSignatureEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class UpdateApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('Application', ApplicationRepo::class);

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

    public function testHandleCommand(): void
    {
        $applicationId = 666;
        $digitalSignatureId = 999;

        $cmd = Cmd::create(['application' => $applicationId, 'digitalSignature' => $digitalSignatureId]);

        $application = m::mock(ApplicationEntity::class);
        $application->expects('updateDigitalSignature')->with(
            $this->refData[RefData::SIG_DIGITAL_SIGNATURE],
            $this->references[DigitalSignatureEntity::class][$digitalSignatureId]
        );

        $this->repoMap['Application']->expects('fetchById')->with($applicationId)->andReturn($application);
        $this->repoMap['Application']->expects('save')->with(m::type(ApplicationEntity::class));

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            ['id' => $applicationId, 'section' => 'undertakings'],
            $this->sideEffectResult('app completion side effect message')
        );

        $result = $this->sut->handleCommand($cmd);
        $this->assertEquals($this->expectedResultMessages(), $result->getMessages());
    }

    private function expectedResultMessages(): array
    {
        return [
            0 => 'Digital signature added to Application 666',
            1 => 'app completion side effect message',
        ];
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DigitalSignature;

use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateSurrender as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Surrender\Snapshot;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\DigitalSignature\UpdateSurrender as Handler;
use Dvsa\Olcs\Api\Domain\Repository\Surrender as SurrenderRepo;
use Dvsa\Olcs\Api\Entity\DigitalSignature as DigitalSignatureEntity;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Surrender as SurrenderEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as HistoryCreator;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\System\Category;
use Mockery as m;

class UpdateSurrenderTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('Surrender', SurrenderRepo::class);

        $this->mockedSmServices = [
            HistoryCreator::class => m::mock(HistoryCreator::class),
        ];

        $this->refData = [
            LicenceEntity::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION => m::mock(RefData::class),
            SurrenderEntity::SURRENDER_STATUS_SIGNED => m::mock(RefData::class),
            RefData::SIG_DIGITAL_SIGNATURE => m::mock(RefData::class),
        ];

        $this->references = [
            DigitalSignatureEntity::class => [
                999 => m::mock(DigitalSignatureEntity::class),
            ],
        ];

        $this->sut = new Handler($this->mockedSmServices[HistoryCreator::class]);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $licenceId = 666;
        $digitalSignatureId = 999;

        $cmd = Cmd::create(['licence' => $licenceId, 'digitalSignature' => $digitalSignatureId]);

        $licence = m::mock(LicenceEntity::class);

        $surrender = m::mock(SurrenderEntity::class);
        $surrender->expects('getLicence')->withNoArgs()->andReturn($licence);
        $surrender->expects('updateDigitalSignature')->with(
            $this->refData[LicenceEntity::LICENCE_STATUS_SURRENDER_UNDER_CONSIDERATION],
            $this->refData[SurrenderEntity::SURRENDER_STATUS_SIGNED],
            $this->refData[RefData::SIG_DIGITAL_SIGNATURE],
            $this->references[DigitalSignatureEntity::class][$digitalSignatureId]
        );

        $this->repoMap['Surrender']
            ->expects('fetchOneByLicenceId')->with($licenceId)->andReturn($surrender);

        $this->repoMap['Surrender']->expects('save')->with(m::type(SurrenderEntity::class));

        $this->expectedSideEffect(
            Snapshot::class,
            ['id' => $licenceId],
            $this->sideEffectResult('snapshot message')
        );

        $this->expectedSideEffect(
            CreateTask::class,
            [
                'category' => Category::CATEGORY_APPLICATION,
                'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_SURRENDER,
                'description' => 'Digital surrender',
                'isClosed' => 'N',
                'urgent' => 'N',
                'licence' => $licenceId,
            ],
            $this->sideEffectResult('task message')
        );

        $this->mockedSmServices[HistoryCreator::class]
            ->expects('create')
            ->with($licence, EventHistoryTypeEntity::EVENT_CODE_SURRENDER_UNDER_CONSIDERATION);

        $result = $this->sut->handleCommand($cmd);
        $this->assertEquals($this->expectedResultMessages(), $result->getMessages());
    }

    private function expectedResultMessages(): array
    {
        return [
            0 => 'Digital signature added to Surrender for Licence 666',
            1 => 'snapshot message',
            2 => 'task message',
        ];
    }
}

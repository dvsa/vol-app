<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DigitalSignature;

use Dvsa\Olcs\Api\Domain\Command\DigitalSignature\UpdateContinuationDetail as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\DigitalSignature\UpdateContinuationDetail as Handler;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as ContinuationDetailRepo;
use Dvsa\Olcs\Api\Entity\DigitalSignature as DigitalSignatureEntity;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class UpdateContinuationDetailTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockRepo('ContinuationDetail', ContinuationDetailRepo::class);

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
        $continuationDetailId = 666;
        $digitalSignatureId = 999;

        $cmd = Cmd::create(['continuationDetail' => $continuationDetailId, 'digitalSignature' => $digitalSignatureId]);

        $continuationDetail = m::mock(ContinuationDetailEntity::class);
        $continuationDetail->expects('updateDigitalSignature')->with(
            $this->refData[RefData::SIG_DIGITAL_SIGNATURE],
            $this->references[DigitalSignatureEntity::class][$digitalSignatureId]
        );

        $this->repoMap['ContinuationDetail']->expects('fetchById')
            ->with($continuationDetailId)
            ->andReturn($continuationDetail);
        $this->repoMap['ContinuationDetail']->expects('save')
            ->with(m::type(ContinuationDetailEntity::class));

        $result = $this->sut->handleCommand($cmd);
        $this->assertEquals($this->expectedResultMessages(), $result->getMessages());
    }

    private function expectedResultMessages(): array
    {
        return [
            0 => 'Digital signature added to continuationDetail 666',
        ];
    }
}

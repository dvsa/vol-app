<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeaseGoodsDiscs;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeasePsvDiscs;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpApplicationsAndPermits;
use Dvsa\Olcs\Api\Domain\Command\Licence\ExpireAllCommunityLicences;
use Dvsa\Olcs\Api\Domain\Command\Licence\ProcessContinuationNotSought as Command;
use Dvsa\Olcs\Api\Domain\Command\LicenceVehicle\RemoveLicenceVehicle;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Tm\DeleteTransportManagerLicence;
use Dvsa\Olcs\Api\Domain\Command\Publication\Licence as PublicationLicenceCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\ProcessContinuationNotSought as Sut;
use Dvsa\Olcs\Api\Domain\Repository\Licence as Repo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\WithdrawableInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as EnqueueFileCommand;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Process Continuation Not Sought test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ProcessContinuationNotSoughtTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('Licence', Repo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            LicenceEntity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT,
        ];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandGoods(): void
    {
        $licenceId = 69;
        $version = 2;

        $command = Command::create(['id' => $licenceId, 'version' => $version]);

        $licenceVehicles = [];
        $licence = m::mock(LicenceEntity::class)
            ->shouldReceive('getId')
            ->andReturn($licenceId)
            ->shouldReceive('isGoods')
            ->andReturn(true)
            ->shouldReceive('getLicenceVehicles')
            ->andReturn($licenceVehicles)
            ->shouldReceive('setStatus')
            ->once()
            ->with($this->mapRefData(LicenceEntity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT))
            ->shouldReceive('setCnsDate')
            ->with(m::type(DateTime::class))
            ->once()
            ->shouldReceive('isNi')
            ->andReturn(false)
            ->twice()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->once()
            ->andReturn($licence)
            ->shouldReceive('save')
            ->with($licence)
            ->once();

        $this->expectedSideEffect(
            CeaseGoodsDiscs::class,
            ['licence' => 69],
            (new Result())->addMessage('Goods discs ceased')
        );

        $this->expectedSideEffect(
            ExpireAllCommunityLicences::class,
            ['id' => $licenceId],
            (new Result())->addMessage('Community licences expired')
        );

        $this->expectedSideEffect(
            RemoveLicenceVehicle::class,
            ['licence' => 69],
            (new Result())->addMessage('Licence vehicles removed')
        );

        $this->expectedSideEffect(
            DeleteTransportManagerLicence::class,
            ['licence' => $licenceId],
            (new Result())->addMessage('Removed transport managers for licence')
        );

        $this->expectedSideEffect(
            PublicationLicenceCmd::class,
            ['id' => $licenceId],
            new Result()
        );

        $this->expectedSideEffect(
            EndIrhpApplicationsAndPermits::class,
            [
                'id' => $licenceId,
                'reason' => WithdrawableInterface::WITHDRAWN_REASON_BY_USER,
                'context' => EndIrhpApplicationsAndPermits::CONTEXT_CNS,
            ],
            new Result()
        );

        $documentId = 1;
        $documentDto = [
            'template' => DocumentEntity::LICENCE_TERMINATED_CONT_FEE_NOT_PAID_GB,
            'query' => [
                'licence'   => $licenceId
            ],
            'licence'       => $licenceId,
            'description'   => Sut::DOCUMENT_DESCRIPTION_GB,
            'category'      => CategoryEntity::CATEGORY_LICENSING,
            'subCategory'   => CategoryEntity::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
            'isExternal'    => false,
        ];
        $documentResult = new Result();
        $documentResult->addId('document', $documentId);
        $this->expectedSideEffect(
            GenerateAndStore::class,
            $documentDto,
            $documentResult
        );

        $printDto = [
            'documentId' => $documentId,
            'jobName'    => Sut::DOCUMENT_DESCRIPTION_GB
        ];
        $this->expectedSideEffect(
            EnqueueFileCommand::class,
            $printDto,
            new Result()
        );

        $response = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'Licence vehicles removed',
                'Removed transport managers for licence',
                'Community licences expired',
                'Goods discs ceased',
                'Licence updated'
            ],
            $response->getMessages()
        );
    }

    public function testHandleCommandPsv(): void
    {
        $licenceId = 69;
        $version = 2;

        $command = Command::create(['id' => $licenceId, 'version' => $version]);

        $psvDiscs = ['PSV_DISCS'];
        $licenceVehicles = ['LICENCE_VEHICLES'];

        $licence = m::mock(LicenceEntity::class)
            ->shouldReceive('getId')
            ->andReturn($licenceId)
            ->shouldReceive('isGoods')
            ->andReturn(false)
            ->shouldReceive('getPsvDiscs')
            ->andReturn($psvDiscs)
            ->shouldReceive('getLicenceVehicles')
            ->andReturn($licenceVehicles)
            ->shouldReceive('setStatus')
            ->once()
            ->with($this->mapRefData(LicenceEntity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT))
            ->shouldReceive('setCnsDate')
            ->with(m::type(DateTime::class))
            ->once()
            ->shouldReceive('isNi')
            ->andReturn(true)
            ->twice()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->once()
            ->andReturn($licence)
            ->shouldReceive('save')
            ->with($licence)
            ->once();

        $this->expectedSideEffect(
            CeasePsvDiscs::class,
            ['licence' => $licenceId],
            (new Result())->addMessage('PSV discs ceased')
        );

        $this->expectedSideEffect(
            ExpireAllCommunityLicences::class,
            ['id' => $licenceId],
            (new Result())->addMessage('Community licences expired')
        );

        $this->expectedSideEffect(
            RemoveLicenceVehicle::class,
            ['licence' => 69],
            (new Result())->addMessage('Licence vehicles removed')
        );

        $this->expectedSideEffect(
            DeleteTransportManagerLicence::class,
            ['licence' => $licenceId],
            (new Result())->addMessage('Removed transport managers for licence')
        );

        $this->expectedSideEffect(
            PublicationLicenceCmd::class,
            ['id' => $licenceId],
            new Result()
        );

        $this->expectedSideEffect(
            EndIrhpApplicationsAndPermits::class,
            [
                'id' => $licenceId,
                'reason' => WithdrawableInterface::WITHDRAWN_REASON_BY_USER
            ],
            new Result()
        );

        $documentId = 1;
        $documentDto = [
            'template' => DocumentEntity::LICENCE_TERMINATED_CONT_FEE_NOT_PAID_NI,
            'query' => [
                'licence'   => $licenceId
            ],
            'licence'       => $licenceId,
            'description'   => Sut::DOCUMENT_DESCRIPTION_NI,
            'category'      => CategoryEntity::CATEGORY_LICENSING,
            'subCategory'   => CategoryEntity::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
            'isExternal'    => false,
        ];
        $documentResult = new Result();
        $documentResult->addId('document', $documentId);
        $this->expectedSideEffect(
            GenerateAndStore::class,
            $documentDto,
            $documentResult
        );

        $printDto = [
            'documentId' => $documentId,
            'jobName'    => Sut::DOCUMENT_DESCRIPTION_NI
        ];
        $this->expectedSideEffect(
            EnqueueFileCommand::class,
            $printDto,
            new Result()
        );

        $response = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'Licence vehicles removed',
                'Removed transport managers for licence',
                'Community licences expired',
                'PSV discs ceased',
                'Licence updated'
            ],
            $response->getMessages()
        );
    }
}

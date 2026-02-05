<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\GenerateChecklistDocument;
use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\Process as Command;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail\Process as CommandHandler;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;
use Mockery as m;

/**
 * ProcessTest
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ProcessTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ContinuationDetail', Repository\ContinuationDetail::class);
        $this->mockRepo('Document', Repository\Document::class);
        $this->mockRepo('Fee', Repository\Fee::class);
        $this->mockRepo('FeeType', Repository\FeeType::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);

        $this->mockedSmServices[TemplateRenderer::class] = m::mock(TemplateRenderer::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'STATUS',
            ContinuationDetail::STATUS_PRINTING,
            Licence::LICENCE_CATEGORY_PSV,
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
            Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            FeeType::FEE_TYPE_CONT,
        ];

        $this->references = [
            Licence::class => [
                7 => m::mock(Licence::class)->makePartial(),
            ],
            Organisation::class => [
                1 => m::mock(Organisation::class)->makePartial(),
            ],
            FeeType::class => [
                999 => m::mock(FeeType::class)->makePartial()
                    ->shouldReceive('getFixedValue')
                    ->andReturn('123.45')
                    ->shouldReceive('getDescription')
                    ->andReturn('Test continuation fee')
                    ->getMock(),
            ],
            TrafficArea::class => [
                'B' => m::mock(TrafficArea::class)->makePartial(),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandNoOp(): void
    {
        $id = 69;

        $data = [
            'id' => $id,
            'user' => 1
        ];

        $command = Command::create($data);

        $continuationDetail = new ContinuationDetail();
        $continuationDetail
            ->setId($id)
            ->setStatus($this->mapRefData('STATUS'));

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($continuationDetail);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['Continuation detail no longer pending'], $result->getMessages());
        $this->assertEquals(['continuationDetail' => $id], $result->getIds());
    }

    public function testHandleCommand(): void
    {
        $id = 69;
        $licenceId = 7;
        $documentId = 101;
        $organisationId = 1;
        $licNo = 'OB1234567';
        $feeId = 102;
        $feeTypeId = 999;
        $userId = 1;

        $data = [
            'id' => $id,
            'user' => $userId
        ];

        $command = Command::create($data);

        $continuationDetail = new ContinuationDetail();
        $continuationDetail
            ->setId($id)
            ->setStatus($this->mapRefData(ContinuationDetail::STATUS_PRINTING))
            ->setLicence($this->mapReference(Licence::class, $licenceId));

        $continuationDetail->getLicence()
            ->setGoodsOrPsv($this->mapRefData(Licence::LICENCE_CATEGORY_PSV))
            ->setLicenceType($this->mapRefData(Licence::LICENCE_TYPE_SPECIAL_RESTRICTED))
            ->setOrganisation($this->mapReference(Organisation::class, $organisationId))
            ->setTrafficArea($this->mapReference(TrafficArea::class, 'B'))
            ->setLicNo($licNo);

        $this->repoMap['SystemParameter']->shouldReceive('getDisabledDigitalContinuations')->andReturn(true);
        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($continuationDetail);

        $this->assertDocumentCreated($id, $userId);

        $document = m::mock();
        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->with($documentId)
            ->once()
            ->andReturn($document);

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('save')
            ->with($continuationDetail)
            ->once();

        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingContinuationFeesByLicenceId')
            ->once()
            ->with($licenceId, m::type(\DateTime::class), true)
            ->andReturn([]);

        $now = new DateTime();
        $this->repoMap['FeeType']
            ->shouldReceive('fetchLatest')
            ->once()
            ->with(
                $this->mapRefData(FeeType::FEE_TYPE_CONT),
                $this->mapRefData(Licence::LICENCE_CATEGORY_PSV),
                $this->mapRefData(Licence::LICENCE_TYPE_SPECIAL_RESTRICTED),
                m::on(
                    // compare date objects
                    fn($arg) => $arg == $now
                ),
                $this->mapReference(TrafficArea::class, 'B')
            )
            ->andReturn($this->mapReference(FeeType::class, $feeTypeId));

        $feeResult = new Result();
        $feeResult
            ->addId('fee', $feeId)
            ->addMessage('Fee created');
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee::class,
            [
                'feeType' => $feeTypeId,
                'feeStatus' => Fee::STATUS_OUTSTANDING,
                'amount' => '123.45',
                'invoicedDate' => $now->format('Y-m-d'),
                'licence' => $licenceId,
                'description' => 'Test continuation fee for licence OB1234567',
                'application' => null,
                'busReg' => null,
                'task' => null,
                'irfoGvPermit' => null,
            ],
            $feeResult
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame($document, $continuationDetail->getChecklistDocument());

        $this->assertEquals(
            [
                'Document dispatched',
                'Fee created',
                'ContinuationDetail updated',
            ],
            $result->getMessages()
        );
        $this->assertEquals(
            [
                'continuationDetail' => $id,
                'document' => $documentId,
                'fee' => $feeId,
            ],
            $result->getIds()
        );
    }

    public function testHandleCommandNiGoods(): void
    {
        $id = 69;
        $licenceId = 7;
        $documentId = 101;
        $organisationId = 1;
        $licNo = 'ON1234567';
        $feeId = 102;
        $feeTypeId = 999;
        $userId = 1;

        $data = [
            'id' => $id,
            'user' => $userId
        ];

        $command = Command::create($data);

        $licence = $this->mapReference(Licence::class, $licenceId);
        $licence->shouldReceive('getNiFlag')->andReturn('Y');

        $continuationDetail = new ContinuationDetail();
        $continuationDetail
            ->setId($id)
            ->setStatus($this->mapRefData(ContinuationDetail::STATUS_PRINTING))
            ->setLicence($licence);

        $continuationDetail->getLicence()
            ->setGoodsOrPsv($this->mapRefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE))
            ->setLicenceType($this->mapRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL))
            ->setOrganisation($this->mapReference(Organisation::class, $organisationId))
            ->setTrafficArea($this->mapReference(TrafficArea::class, 'N'))
            ->setLicNo($licNo);

        $this->repoMap['SystemParameter']->shouldReceive('getDisabledDigitalContinuations')->andReturn(true);
        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($continuationDetail);

        $this->assertDocumentCreated($id, $userId);

        $document = m::mock();
        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->with($documentId)
            ->once()
            ->andReturn($document);

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('save')
            ->with($continuationDetail)
            ->once();

        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingContinuationFeesByLicenceId')
            ->once()
            ->with($licenceId, m::type(\DateTime::class), true)
            ->andReturn([]);

        $now = new DateTime();
        $this->repoMap['FeeType']
            ->shouldReceive('fetchLatest')
            ->once()
            ->with(
                $this->mapRefData(FeeType::FEE_TYPE_CONT),
                $this->mapRefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE),
                $this->mapRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL),
                m::on(
                    // compare date objects
                    fn($arg) => $arg == $now
                ),
                $this->mapReference(TrafficArea::class, 'N')
            )
            ->andReturn($this->mapReference(FeeType::class, $feeTypeId));

        $feeResult = new Result();
        $feeResult
            ->addId('fee', $feeId)
            ->addMessage('Fee created');
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee::class,
            [
                'feeType' => $feeTypeId,
                'feeStatus' => Fee::STATUS_OUTSTANDING,
                'amount' => '123.45',
                'invoicedDate' => $now->format('Y-m-d'),
                'licence' => $licenceId,
                'description' => 'Test continuation fee for licence ON1234567',
                'application' => null,
                'busReg' => null,
                'task' => null,
                'irfoGvPermit' => null,
            ],
            $feeResult
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame($document, $continuationDetail->getChecklistDocument());

        $this->assertEquals(
            [
                'Document dispatched',
                'Fee created',
                'ContinuationDetail updated',
            ],
            $result->getMessages()
        );
        $this->assertEquals(
            [
                'continuationDetail' => $id,
                'document' => $documentId,
                'fee' => $feeId,
            ],
            $result->getIds()
        );
    }

    private function assertFeeCreated(int $feeId = 102, int $feeTypeId = 999, int $licenceId = 7): void
    {
        $now = new DateTime();
        $this->repoMap['FeeType']
            ->shouldReceive('fetchLatest')
            ->once()
            ->with(
                $this->mapRefData(FeeType::FEE_TYPE_CONT),
                $this->mapRefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE),
                $this->mapRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL),
                m::on(
                    // compare date objects
                    fn($arg) => $arg == $now
                ),
                $this->mapReference(TrafficArea::class, 'B')
            )
            ->andReturn($this->mapReference(FeeType::class, $feeTypeId));

        $feeResult = new Result();
        $feeResult
            ->addId('fee', $feeId)
            ->addMessage('Fee created');
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee::class,
            [
                'feeType' => $feeTypeId,
                'feeStatus' => Fee::STATUS_OUTSTANDING,
                'amount' => '123.45',
                'invoicedDate' => $now->format('Y-m-d'),
                'licence' => $licenceId,
                'description' => 'Test continuation fee for licence OB1234567',
                'application' => null,
                'busReg' => null,
                'task' => null,
                'irfoGvPermit' => null,
            ],
            $feeResult
        );
    }

    private function assertDocumentCreated(int $continuationDetailId = 1, int $userId = 1, bool $enforcePrint = false): void
    {
        $dtoData = [
            'id' => $continuationDetailId,
            'user' => $userId,
            'enforcePrint' => $enforcePrint,

        ];

        $docResult = new Result();
        $docResult->addId('document', 101);
        $docResult->addMessage('Document dispatched');

        $this->expectedSideEffect(GenerateChecklistDocument::class, $dtoData, $docResult);
    }

    private function setupContinuationDetail(mixed $id): mixed
    {
        $licenceId = 7;
        $licNo = 'OB1234567';
        $organisationId = 1;

        $continuationDetail = new ContinuationDetail();
        $continuationDetail
            ->setId($id)
            ->setStatus($this->mapRefData(ContinuationDetail::STATUS_PRINTING))
            ->setLicence($this->mapReference(Licence::class, $licenceId));

        $continuationDetail->getLicence()
            ->setExpiryDate('2017-08-23')
            ->setGoodsOrPsv($this->mapRefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE))
            ->setLicenceType($this->mapRefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL))
            ->setOrganisation($this->mapReference(Organisation::class, $organisationId))
            ->setTrafficArea($this->mapReference(TrafficArea::class, 'B'))
            ->setLicNo($licNo);

        return $continuationDetail;
    }

    private function assertNonDigital(): void
    {
        $id = 69;
        $documentId = 101;
        $feeId = 102;
        $userId = 1;

        $data = [
            'id' => $id,
            'user' => $userId
        ];

        $command = Command::create($data);

        $continuationDetail = $this->setupContinuationDetail($id);

        $this->repoMap['SystemParameter']
            ->shouldReceive('getDisabledDigitalContinuations')->andReturn(false);
        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($continuationDetail);

        $this->assertDocumentCreated($id, $userId);

        $this->repoMap['Document']
            ->shouldReceive('fetchById')->with($documentId)->once();
        $this->repoMap['ContinuationDetail']
            ->shouldReceive('save')->with($continuationDetail)->once();
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingContinuationFeesByLicenceId')->once()->andReturn([]);

        $this->assertFeeCreated();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'Document dispatched',
                'Fee created',
                'ContinuationDetail updated',
            ],
            $result->getMessages()
        );
        $this->assertEquals(
            [
                'continuationDetail' => $id,
                'document' => $documentId,
                'fee' => $feeId,
            ],
            $result->getIds()
        );
    }

    public function testHandleCommandDigitalEmailDisbaled(): void
    {
        $this->references[Organisation::class][1]
            ->shouldReceive('getAllowEmail')->with()->once()->andReturn('N');
        $this->assertNonDigital();
    }

    public function testHandleCommandDigitalNoAdminEmailAddresses(): void
    {
        $this->references[Organisation::class][1]
            ->shouldReceive('getAllowEmail')->with()->once()->andReturn('Y')
            ->shouldReceive('getAdminEmailAddresses')->with()->once()->andReturn([]);
        $this->assertNonDigital();
    }

    public function testHandleCommandDigital(): void
    {
        $id = 69;
        $userId = 1;

        $data = [
            'id' => $id,
            'user' => $userId
        ];

        $command = Command::create($data);

        $continuationDetail = $this->setupContinuationDetail($id);

        $this->references[Organisation::class][1]
            ->shouldReceive('getAllowEmail')->with()->once()->andReturn('Y')
            ->shouldReceive('getAdminEmailAddresses')->with()->twice()->andReturn(['a@a.com']);

        $this->repoMap['SystemParameter']
            ->shouldReceive('getDisabledDigitalContinuations')->andReturn(false);
        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($continuationDetail);

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('save')->with($continuationDetail)->once();
        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingContinuationFeesByLicenceId')->once()->andReturn([]);

        $this->assertFeeCreated();

        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->once()->with(
            m::type(Message::class),
            'digital-continuation',
            [
                'licNo' => 'OB1234567',
                'continuationDate' => '23 August 2017',
                'isGoods' => true,
                'isPsv' => false,
                'isSpecialRestricted' => false,
                'feeAmount' => 123.45,
                'continueLicenceUrl' => sprintf('http://selfserve/continuation/%d', $id),
            ],
            'default'
        );
        $this->expectedSideEffect(SendEmail::class, [], new Result());

        $this->sut->handleCommand($command);
    }
}

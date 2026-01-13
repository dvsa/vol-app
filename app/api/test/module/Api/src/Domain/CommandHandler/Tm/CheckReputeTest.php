<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Command\Tm\CheckReputeProcessDocument;
use Dvsa\Olcs\Api\Domain\CommandHandler\Tm\CheckRepute;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Service\Nr\CheckGoodRepute;
use Dvsa\Olcs\Api\Service\Nr\InrClient;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager as TransportManagerRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Tm\CheckRepute as CheckReputeCmd;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;

class CheckReputeTest extends AbstractCommandHandlerTestCase
{
    protected $sut;
    private m\mockInterface $inrClient;
    private m\mockInterface $checkGoodReputeService;

    public function setUp(): void
    {
        $this->inrClient = m::mock(InrClient::class);
        $this->checkGoodReputeService = m::mock(CheckGoodRepute::class);

        $this->sut = new CheckRepute($this->inrClient, $this->checkGoodReputeService);
        $this->mockRepo('TransportManager', TransportManagerRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $tmId = 999;
        $createdDocumentId = 888;
        $documentCreateMessage = 'document create message';
        $documentProcessMessage = 'document process message';
        $cmd = CheckReputeCmd::create(['id' => $tmId]);
        $tmName = 'tm name';
        $xmlRequest = '<?xml version="1.0" encoding="UTF-8"?>';
        $xmlResponse = ' <?xml version="1.0"?> ';
        $trimmedXmlResponse = '<?xml version="1.0"?>';
        $tmEntity = m::mock(TransportManagerEntity::class);
        $tmEntity->expects('getFullName')->withNoArgs()->andReturn($tmName);

        $this->repoMap['TransportManager']->expects('fetchById')->with($tmId)->andReturn($tmEntity);

        $this->checkGoodReputeService->expects('create')->with($tmEntity)->andReturn($xmlRequest);
        $this->inrClient->expects('makeRequestReturnResponse')->with($xmlRequest)->andReturn($xmlResponse);
        $this->inrClient->expects('getLastStatusCode')->withNoArgs()->andReturn(200);
        $this->inrClient->expects('close')->withNoArgs()->andReturnNull();

        $documentUploadData = [
            'content' => base64_encode($trimmedXmlResponse),
            'category' => CategoryEntity::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_REPUTE_CHECK,
            'filename' => 'cgr-response.xml',
            'description' => sprintf($this->sut::DESC_XML_RESPONSE, $tmName),
            'transportManager' => $tmId,
        ];

        $documentResult = new Result();
        $documentResult->addId('document', $createdDocumentId);
        $documentResult->addMessage($documentCreateMessage);

        $this->expectedSideEffect(Upload::class, $documentUploadData, $documentResult);

        $processDocumentData = ['id' => $createdDocumentId];
        $processDocumentResult = new Result();
        $processDocumentResult->addMessage($documentProcessMessage);

        $this->expectedSideEffect(CheckReputeProcessDocument::class, $processDocumentData, $processDocumentResult);

        $result = $this->sut->handleCommand($cmd);
        $messages = $result->getMessages();

        $this->assertEquals($tmId, $result->getId('Transport Manager'));
        $this->assertEquals($createdDocumentId, $result->getId('document'));
        $this->assertContains('Repute check response received from INR: tm name', $messages);
        $this->assertContains($documentCreateMessage, $messages);
        $this->assertContains($documentProcessMessage, $messages);
    }

    public function testHandleCommandNoAddressData(): void
    {
        $tmId = 999;
        $taskCreateMessage = 'task create message';
        $cmd = CheckReputeCmd::create(['id' => $tmId]);
        $tmName = 'tm name';
        $tmEntity = m::mock(TransportManagerEntity::class);
        $tmEntity->expects('getFullName')->withNoArgs()->andReturn($tmName);

        $this->repoMap['TransportManager']->expects('fetchById')->with($tmId)->andReturn($tmEntity);

        $this->checkGoodReputeService->expects('create')->with($tmEntity)->andThrow(ForbiddenException::class);

        $this->failureTask($tmId, $tmName, $taskCreateMessage);

        $result = $this->sut->handleCommand($cmd);
        $messages = $result->getMessages();
        $this->assertNotContains('Repute check response received from INR: tm name', $messages);
        $this->assertContains($taskCreateMessage, $messages);
    }

    public function testHandleCommandAdapterException(): void
    {
        $tmId = 999;
        $taskCreateMessage = 'task create message';
        $cmd = CheckReputeCmd::create(['id' => $tmId]);
        $tmName = 'tm name';
        $xmlRequest = '<?xml version="1.0" encoding="UTF-8"?>';
        $tmEntity = m::mock(TransportManagerEntity::class);
        $tmEntity->expects('getFullName')->withNoArgs()->andReturn($tmName);

        $this->repoMap['TransportManager']->expects('fetchById')->with($tmId)->andReturn($tmEntity);

        $this->checkGoodReputeService->expects('create')->with($tmEntity)->andReturn($xmlRequest);
        $this->inrClient->expects('makeRequestReturnResponse')
            ->with($xmlRequest)
            ->andThrow(\Exception::class);

        $this->failureTask($tmId, $tmName, $taskCreateMessage);

        $result = $this->sut->handleCommand($cmd);
        $messages = $result->getMessages();
        $this->assertNotContains('Repute check response received from INR: tm name', $messages);
        $this->assertContains($taskCreateMessage, $messages);
    }

    public function testHandleCommandFailureStatusCode(): void
    {
        $tmId = 999;
        $taskCreateMessage = 'task create message';
        $cmd = CheckReputeCmd::create(['id' => $tmId]);
        $tmName = 'tm name';
        $xmlRequest = '<?xml version="1.0" encoding="UTF-8"?>';
        $xmlResponse = ' <?xml version="1.0"?> ';
        $tmEntity = m::mock(TransportManagerEntity::class);
        $tmEntity->expects('getFullName')->withNoArgs()->andReturn($tmName);

        $this->repoMap['TransportManager']->expects('fetchById')->with($tmId)->andReturn($tmEntity);

        $this->checkGoodReputeService->expects('create')->with($tmEntity)->andReturn($xmlRequest);
        $this->inrClient->expects('makeRequestReturnResponse')->with($xmlRequest)->andReturn($xmlResponse);
        $this->inrClient->expects('getLastStatusCode')->withNoArgs()->andReturn(500);

        $this->failureTask($tmId, $tmName, $taskCreateMessage);

        $result = $this->sut->handleCommand($cmd);
        $messages = $result->getMessages();
        $this->assertNotContains('Repute check response received from INR: tm name', $messages);
        $this->assertContains($taskCreateMessage, $messages);
    }

    private function failureTask(int $tmId, string $tmName, string $taskCreateMessage): void
    {
        $taskData = [
            'category' => CategoryEntity::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_REPUTE_CHECK,
            'description' => sprintf($tmName . ': repute check unavailable'),
            'actionDate' => date('Y-m-d'),
            'transportManager' => $tmId,
        ];

        $taskResult = new Result();
        $taskResult->addMessage($taskCreateMessage);

        $this->expectedSideEffect(CreateTask::class, $taskData, $taskResult);
    }
}

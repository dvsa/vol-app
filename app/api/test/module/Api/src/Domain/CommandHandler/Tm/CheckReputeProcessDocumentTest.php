<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Command\Tm\CheckReputeProcessDocument as CheckReputeProcessDocumentCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Tm\CheckReputeProcessDocument;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Dvsa\Olcs\Api\Service\Nr\Mapping\CgrResponseXml;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TmReputeCheck\Generator as TmReputeCheckGenerator;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;

class CheckReputeProcessDocumentTest extends AbstractCommandHandlerTestCase
{
    protected $sut;
    private m\mockInterface $fileUploader;
    private m\mockInterface $cgrInputFilter;
    private m\mockInterface $cgrXmlMapping;
    private m\mockInterface $snapshotGenerator;

    public function setUp(): void
    {
        $this->fileUploader = m::mock(ContentStoreFileUploader::class);
        $this->cgrInputFilter = m::mock(Input::class);
        $this->cgrXmlMapping = m::mock(CgrResponseXml::class);
        $this->snapshotGenerator = m::mock(TmReputeCheckGenerator::class);

        $this->sut = new CheckReputeProcessDocument(
            $this->fileUploader,
            $this->cgrInputFilter,
            $this->cgrXmlMapping,
            $this->snapshotGenerator
        );

        $this->mockRepo('Document', DocumentRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $tmId = 999;
        $documentId = 888;
        $documentIdentifier = 'identifier';
        $snapshotUploadMessage = 'snapshot upload message';
        $cmd = CheckReputeProcessDocumentCmd::create(['id' => $documentId]);
        $tmName = 'tm name';
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>';
        $mappedXml = ['mapped'];
        $html = ' <html></html> ';
        $trimmedHtml = '<html></html>';

        $tmEntity = m::mock(TransportManagerEntity::class);
        $tmEntity->expects('getId')->withNoArgs()->andReturn($tmId);
        $tmEntity->expects('getFullName')->withNoArgs()->andReturn($tmName);

        $documentEntity = m::mock(DocumentEntity::class);
        $documentEntity->expects('getTransportManager')->withNoArgs()->andReturn($tmEntity);
        $documentEntity->expects('getIdentifier')->withNoArgs()->andReturn($documentIdentifier);

        $this->repoMap['Document']->expects('fetchById')->with($documentId)->andReturn($documentEntity);

        $xmlFile = m::mock(File::class);
        $xmlFile->expects('getContent')->withNoArgs()->andReturn($xmlContent);

        $this->fileUploader->expects('download')->with($documentIdentifier)->andReturn($xmlFile);

        $domDocument = m::mock(\DOMDocument::class);
        $this->cgrInputFilter->expects('setValue')->with($xmlContent)->andReturnSelf();
        $this->cgrInputFilter->expects('isValid')->withNoArgs()->andReturnTrue();
        $this->cgrInputFilter->expects('getValue')->withNoArgs()->andReturn($domDocument);

        $this->cgrXmlMapping->expects('mapData')->with($domDocument)->andReturn($mappedXml);

        $this->snapshotGenerator->expects('generate')->with($mappedXml)->andReturn($html);

        $snapshotUploadData = [
            'content' => base64_encode($trimmedHtml),
            'category' => CategoryEntity::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_REPUTE_CHECK,
            'filename' => 'cgr-snapshot.html',
            'description' => sprintf($this->sut::DESC_SNAPSHOT, $tmName),
            'transportManager' => $tmId,
        ];

        $snapshotUploadResult = new Result();
        $snapshotUploadResult->addMessage($snapshotUploadMessage);

        $this->expectedSideEffect(Upload::class, $snapshotUploadData, $snapshotUploadResult);

        $result = $this->sut->handleCommand($cmd);
        $messages = $result->getMessages();

        $this->assertContains('Repute check snapshot saved: tm name', $messages);
        $this->assertContains($snapshotUploadMessage, $messages);
    }

    public function testHandleCommandSnapshotGeneratorException(): void
    {
        $tmId = 999;
        $documentId = 888;
        $documentIdentifier = 'identifier';
        $taskCreateMessage = 'task create message';
        $cmd = CheckReputeProcessDocumentCmd::create(['id' => $documentId]);
        $tmName = 'tm name';
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>';
        $mappedXml = ['mapped'];

        $tmEntity = m::mock(TransportManagerEntity::class);
        $tmEntity->expects('getId')->withNoArgs()->andReturn($tmId);
        $tmEntity->expects('getFullName')->withNoArgs()->andReturn($tmName);

        $documentEntity = m::mock(DocumentEntity::class);
        $documentEntity->expects('getTransportManager')->withNoArgs()->andReturn($tmEntity);
        $documentEntity->expects('getIdentifier')->withNoArgs()->andReturn($documentIdentifier);

        $this->repoMap['Document']->expects('fetchById')->with($documentId)->andReturn($documentEntity);

        $xmlFile = m::mock(File::class);
        $xmlFile->expects('getContent')->withNoArgs()->andReturn($xmlContent);

        $this->fileUploader->expects('download')->with($documentIdentifier)->andReturn($xmlFile);

        $domDocument = m::mock(\DOMDocument::class);
        $this->cgrInputFilter->expects('setValue')->with($xmlContent)->andReturnSelf();
        $this->cgrInputFilter->expects('isValid')->withNoArgs()->andReturnTrue();
        $this->cgrInputFilter->expects('getValue')->withNoArgs()->andReturn($domDocument);

        $this->cgrXmlMapping->expects('mapData')->with($domDocument)->andReturn($mappedXml);

        $this->snapshotGenerator->expects('generate')->with($mappedXml)->andThrow(\Exception::class);

        $this->failureTask($tmId, $tmName, $taskCreateMessage);

        $result = $this->sut->handleCommand($cmd);
        $messages = $result->getMessages();

        $this->assertNotContains('Repute check snapshot saved: tm name', $messages);
        $this->assertContains($taskCreateMessage, $messages);
    }

    public function testHandleCommandXmlInvalid(): void
    {
        $tmId = 999;
        $documentId = 888;
        $documentIdentifier = 'identifier';
        $taskCreateMessage = 'task create message';
        $cmd = CheckReputeProcessDocumentCmd::create(['id' => $documentId]);
        $tmName = 'tm name';
        $xmlContent = 'invalid';

        $tmEntity = m::mock(TransportManagerEntity::class);
        $tmEntity->expects('getId')->withNoArgs()->andReturn($tmId);
        $tmEntity->expects('getFullName')->withNoArgs()->andReturn($tmName);

        $documentEntity = m::mock(DocumentEntity::class);
        $documentEntity->expects('getTransportManager')->withNoArgs()->andReturn($tmEntity);
        $documentEntity->expects('getIdentifier')->withNoArgs()->andReturn($documentIdentifier);

        $this->repoMap['Document']->expects('fetchById')->with($documentId)->andReturn($documentEntity);

        $xmlFile = m::mock(File::class);
        $xmlFile->expects('getContent')->withNoArgs()->andReturn($xmlContent);

        $this->fileUploader->expects('download')->with($documentIdentifier)->andReturn($xmlFile);

        $this->cgrInputFilter->expects('setValue')->with($xmlContent)->andReturnSelf();
        $this->cgrInputFilter->expects('isValid')->withNoArgs()->andReturnFalse();
        $this->cgrInputFilter->expects('getMessages')->withNoArgs()->andReturn(['messages']);
        $this->cgrInputFilter->expects('getValue')->never();

        $this->failureTask($tmId, $tmName, $taskCreateMessage);

        $result = $this->sut->handleCommand($cmd);
        $messages = $result->getMessages();

        $this->assertNotContains('Repute check snapshot saved: tm name', $messages);
        $this->assertContains($taskCreateMessage, $messages);
    }

    public function testHandleCommandNoXmlFile(): void
    {
        $tmId = 999;
        $documentId = 888;
        $documentIdentifier = 'identifier';
        $taskCreateMessage = 'task create message';
        $cmd = CheckReputeProcessDocumentCmd::create(['id' => $documentId]);
        $tmName = 'tm name';

        $tmEntity = m::mock(TransportManagerEntity::class);
        $tmEntity->expects('getId')->withNoArgs()->andReturn($tmId);
        $tmEntity->expects('getFullName')->withNoArgs()->andReturn($tmName);

        $documentEntity = m::mock(DocumentEntity::class);
        $documentEntity->expects('getTransportManager')->withNoArgs()->andReturn($tmEntity);
        $documentEntity->expects('getIdentifier')->withNoArgs()->andReturn($documentIdentifier);

        $this->repoMap['Document']->expects('fetchById')->with($documentId)->andReturn($documentEntity);

        $xmlFile = m::mock(\stdClass::class);
        $this->cgrInputFilter->expects('getContent')->never();

        $this->fileUploader->expects('download')->with($documentIdentifier)->andReturn($xmlFile);

        $this->failureTask($tmId, $tmName, $taskCreateMessage);

        $result = $this->sut->handleCommand($cmd);
        $messages = $result->getMessages();

        $this->assertNotContains('Repute check snapshot saved: tm name', $messages);
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

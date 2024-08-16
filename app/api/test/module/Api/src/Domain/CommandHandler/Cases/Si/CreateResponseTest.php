<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Si;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si\CreateResponse;
use Dvsa\Olcs\Transfer\Command\Cases\Si\CreateResponse as CreateErruResponseCmd;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Domain\Repository\ErruRequest as ErruRequestRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as ErruRequestEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Service\Nr\MsiResponse as MsiResponseService;
use LmcRbacMvc\Service\AuthorizationService;
use LmcRbacMvc\Identity\IdentityInterface;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;

/**
 * CreateResponseTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CreateResponseTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateResponse();
        $this->mockRepo('Cases', CasesRepo::class);
        $this->mockRepo('ErruRequest', ErruRequestRepo::class);
        $this->mockRepo('Document', DocumentRepo::class);

        $this->mockedSmServices = [
            MsiResponseService::class => m::mock(MsiResponseService::class),
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        $this->refData = [
            ErruRequestEntity::QUEUED_CASE_TYPE
        ];

        parent::setUp();
    }

    /**
     * Tests creation and queuing of the Msi response
     */
    public function testHandleCommand()
    {
        $responseDate = '2015-12-25 00:00:00';
        $xml = 'xml string';
        $userId = 111;
        $caseId = 333;
        $licenceId = 444;
        $documentId = 555;
        $erruRequestId = 777;
        $command = CreateErruResponseCmd::create(['case' => $caseId]);
        $notificationNumber = 'notification number guid';

        $user = m::mock(UserEntity::class);
        $user->shouldReceive('getId')->andReturn($userId);

        $responseDocument = m::mock(DocumentEntity::class);

        $documentResult = new Result();
        $documentResult->addId('document', $documentId);

        $documentData = [
            'content' => base64_encode($xml),
            'category' => CategoryEntity::CATEGORY_COMPLIANCE,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_NR,
            'filename' => 'msiresponse.xml',
            'description' => sprintf(CreateResponse::RESPONSE_DOCUMENT_DESCRIPTION, $notificationNumber),
            'case' => $caseId,
            'licence' => $licenceId
        ];

        $this->expectedSideEffect(UploadCmd::class, $documentData, $documentResult);

        $erruRequest = m::mock(ErruRequestEntity::class);
        $erruRequest
            ->shouldReceive('queueErruResponse')
            ->once()
            ->with(
                $user,
                m::type(\DateTime::class),
                $responseDocument,
                $this->refData[ErruRequestEntity::QUEUED_CASE_TYPE]
            );
        $erruRequest->shouldReceive('getNotificationNumber')->once()->andReturn($notificationNumber);
        $erruRequest->shouldReceive('getId')->andReturn($erruRequestId);

        $case = m::mock(CasesEntity::class);
        $case->shouldReceive('getId')->times(2)->andReturn($caseId);
        $case->shouldReceive('getErruRequest')->once()->andReturn($erruRequest);
        $case->shouldReceive('getLicence->getId')->once()->andReturn($licenceId);

        $this->repoMap['Cases']->shouldReceive('fetchById')->once()->with($caseId)->andReturn($case);
        $this->repoMap['ErruRequest']->shouldReceive('save')->once()->with(m::type(ErruRequestEntity::class));
        $this->repoMap['Document']->shouldReceive('fetchById')->once()->with($documentId)->andReturn($responseDocument);

        $rbacIdentity = m::mock(IdentityInterface::class);
        $rbacIdentity->shouldReceive('getUser')->andReturn($user);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $this->mockedSmServices[MsiResponseService::class]
            ->shouldReceive('getResponseDateTime')
            ->once()
            ->andReturn($responseDate);

        $this->mockedSmServices[MsiResponseService::class]
            ->shouldReceive('create')
            ->once()
            ->with($case)
            ->andReturn($xml);

        $this->expectedQueueSideEffect(
            $erruRequestId,
            QueueEntity::TYPE_SEND_MSI_RESPONSE,
            ['id' => $erruRequestId]
        );

        $result = $this->sut->handleCommand($command);
        $this->assertInstanceOf(Result::class, $result);
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Si;

use Dvsa\Olcs\Api\Domain\Command\Cases\Si\SendResponse;
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
use Dvsa\Olcs\Api\Service\Nr\MsiResponse as MsiResponseService;
use LmcRbacMvc\Service\AuthorizationService;
use LmcRbacMvc\Identity\IdentityInterface;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;

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
            ErruRequestEntity::SENT_CASE_TYPE
        ];

        parent::setUp();
    }

    /**
     * Tests creation and queuing of the Msi response
     */
    public function testHandleCommand(): void
    {
        $responseDate = '2015-12-25 00:00:00';
        $xml = 'xml string';
        $caseId = 333;
        $licenceId = 444;
        $documentId = 555;
        $erruRequestId = 777;
        $command = CreateErruResponseCmd::create(['case' => $caseId]);
        $notificationNumber = 'notification number guid';

        $user = m::mock(UserEntity::class);

        $responseDocument = m::mock(DocumentEntity::class);

        $documentMessage = 'document message';
        $documentResult = $this->sideEffectResult($documentMessage);
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

        $erruRequest->expects('queueErruResponse')
            ->with($user, m::type(\DateTime::class), $responseDocument)
            ->andReturnNull();
        $erruRequest->expects('getNotificationNumber')->withNoArgs()->andReturn($notificationNumber);
        $erruRequest->expects('getId')->withNoArgs()->andReturn($erruRequestId);

        $case = m::mock(CasesEntity::class);
        $case->expects('getId')->withNoArgs()->andReturn($caseId);
        $case->expects('getErruRequest')->withNoArgs()->andReturn($erruRequest);
        $case->shouldReceive('getLicence->getId')->once()->andReturn($licenceId);
        $case->expects('hasErruRequestedPenalties')->withNoArgs()->andReturnTrue();

        $this->repoMap['Cases']->expects('fetchById')->with($caseId)->andReturn($case);
        $this->repoMap['ErruRequest']->expects('save')->with(m::type(ErruRequestEntity::class));
        $this->repoMap['Document']->expects('fetchById')->with($documentId)->andReturn($responseDocument);

        $rbacIdentity = m::mock(IdentityInterface::class);
        $rbacIdentity->expects('getUser')->withNoArgs()->andReturn($user);

        $this->mockedSmServices[AuthorizationService::class]
            ->expects('getIdentity')
            ->withNoArgs()
            ->andReturn($rbacIdentity);

        $this->mockedSmServices[MsiResponseService::class]
            ->expects('getResponseDateTime')
            ->withNoArgs()
            ->andReturn($responseDate);

        $this->mockedSmServices[MsiResponseService::class]
            ->expects('create')
            ->with($case)
            ->andReturn($xml);

        $msiSentMessage = 'sent message';

        $this->expectedSideEffect(
            SendResponse::class,
            ['id' => $erruRequestId],
            $this->sideEffectResult($msiSentMessage)
        );

        $result = $this->sut->handleCommand($command);
        $messages = $result->getMessages();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($caseId, $result->getId('case'));
        $this->assertEquals($erruRequestId, $result->getId('erruRequest'));
        $this->assertEquals($documentId, $result->getId('document'));
        $this->assertContains(CreateResponse::MSG_RESPONSE_CREATED, $messages);
        $this->assertNotContains(CreateResponse::MSG_RESPONSE_NOT_REQUIRED, $messages);
        $this->assertContains($msiSentMessage, $messages);
        $this->assertContains($documentMessage, $messages);
    }

    /**
     * No response is sent when there are no requested penalties
     */
    public function testHandleCommandNoRequestedPenalties(): void
    {
        $caseId = 333;
        $erruRequestId = 777;
        $command = CreateErruResponseCmd::create(['case' => $caseId]);

        $erruRequest = m::mock(ErruRequestEntity::class);
        $erruRequest->expects('getId')->withNoArgs()->andReturn($erruRequestId);
        $erruRequest->expects('setMsiType')
            ->with($this->refData[ErruRequestEntity::SENT_CASE_TYPE])
            ->andReturnSelf();

        $case = m::mock(CasesEntity::class);
        $case->expects('getErruRequest')->withNoArgs()->andReturn($erruRequest);
        $case->expects('hasErruRequestedPenalties')->withNoArgs()->andReturnFalse();

        $this->repoMap['Cases']->expects('fetchById')->with($caseId)->andReturn($case);
        $this->repoMap['ErruRequest']->expects('save')->with(m::type(ErruRequestEntity::class));

        //no attempt to create msi response
        $this->mockedSmServices[MsiResponseService::class]
            ->expects('create')
            ->never();

        $result = $this->sut->handleCommand($command);
        $messages = $result->getMessages();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($caseId, $result->getId('case'));
        $this->assertEquals($erruRequestId, $result->getId('erruRequest'));
        $this->assertContains(CreateResponse::MSG_RESPONSE_NOT_REQUIRED, $messages);
        $this->assertNotContains(CreateResponse::MSG_RESPONSE_CREATED, $messages);
    }
}

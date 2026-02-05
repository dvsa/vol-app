<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Si;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Api\Entity\Si\ErruRequestFailure;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si\ComplianceEpisode;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepo;
use Dvsa\Olcs\Api\Domain\Repository\SiCategory as SiCategoryRepo;
use Dvsa\Olcs\Api\Domain\Repository\SiCategoryType as SiCategoryTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\SiPenaltyImposedType as SiPenaltyImposedTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\SiPenaltyRequestedType as SiPenaltyRequestedTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\ErruRequest as ErruRequestRepo;
use Dvsa\Olcs\Api\Domain\Repository\ErruRequestFailure as ErruRequestFailureRepo;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as ErruRequestEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategoryType as SiCategoryTypeEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyImposedType as SiPenaltyImposedTypeEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyRequestedType as SiPenaltyRequestedTypeEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategory as SiCategoryEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentLinks as UpdateDocLinksCmd;
use Dvsa\Olcs\Api\Service\Nr\InputFilter\XmlStructureInputFactory;
use Dvsa\Olcs\Api\Service\Nr\InputFilter\SeriousInfringementInputFactory;
use Dvsa\Olcs\Api\Service\Nr\InputFilter\ComplianceEpisodeInputFactory;
use Dvsa\Olcs\Api\Domain\Command\Cases\Si\ComplianceEpisode as ComplianceEpisodeCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendErruErrors as SendErruErrorsCmd;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Api\Service\Nr\Mapping\ComplianceEpisodeXml as ComplianceEpisodeXmlMapping;

class ComplianceEpisodeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ComplianceEpisode();
        $this->mockRepo('Cases', CasesRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('Country', CountryRepo::class);
        $this->mockRepo('SiCategory', SiCategoryRepo::class);
        $this->mockRepo('SiCategoryType', SiCategoryTypeRepo::class);
        $this->mockRepo('SiPenaltyImposedType', SiPenaltyImposedTypeRepo::class);
        $this->mockRepo('SiPenaltyRequestedType', SiPenaltyRequestedTypeRepo::class);
        $this->mockRepo('ErruRequest', ErruRequestRepo::class);
        $this->mockRepo('ErruRequestFailure', ErruRequestFailureRepo::class);
        $this->mockRepo('Document', DocumentRepo::class);

        $this->mockedSmServices = [
            'ComplianceXmlStructure' => m::mock(XmlStructureInputFactory::class),
            'ComplianceEpisodeInput' => m::mock(ComplianceEpisodeInputFactory::class),
            'SeriousInfringementInput' => m::mock(SeriousInfringementInputFactory::class),
            'FileUploader' => m::mock(ContentStoreFileUploader::class),
            'ComplianceEpisodeXmlMapping' => m::mock(ComplianceEpisodeXmlMapping::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CasesEntity::LICENCE_CASE_TYPE,
            ErruRequestEntity::DEFAULT_CASE_TYPE,
            CasesEntity::ERRU_DEFAULT_CASE_CATEGORY,
            CommunityLic::STATUS_ACTIVE,
            'pen_erru_imposed_executed_yes'
        ];

        $this->references = [
            SiPenaltyImposedTypeEntity::class => [
                102 => m::mock(SiPenaltyImposedTypeEntity::class)
            ],
            SiPenaltyRequestedTypeEntity::class => [
                301 => m::mock(SiPenaltyRequestedTypeEntity::class)
            ]
        ];

        parent::initReferences();
    }

    /**
     * Tests processing the XML and creating the serious infringement
     */
    public function testHandleCommand(): void
    {
        $xmlString = 'xml string';
        $documentId = 111;
        $command = ComplianceEpisodeCmd::create(['id' => $documentId]);

        $this->fetchDocumentAndXml($command, $xmlString, $documentId, 1);
        $licenceId = 999;

        //common data
        $workflowId = '20776dc3-5fe7-42d5-b554-09ad12fa25c4';
        $notificationNumber = '0ffefb6b-6344-4a60-9a53-4381c32f98d9';
        $originatingAuthority = 'originating authority';
        $licenceNumber = 'OB1234567';
        $vrm = 'ABC123';
        $transportUndertakingName = 'transport undertaking';
        $memberStateCode = 'PL';
        $checkDate = '2015-12-24';
        $communityLicenceNumber = 'UKGB/OB1234567/00001';
        $totAuthVehicles = 10;

        //imposed erru
        $siPenaltyImposedType = 102;
        $startDate = '2016-03-14';
        $endDate = '2016-04-14';
        $finalDecisionDate = '2016-02-14';
        $executed = 'Yes';
        $executedRefData = 'pen_erru_imposed_executed_yes';

        $imposedErru = [
            'penaltyImposedIdentifier' => 1111,
            'finalDecisionDate' => $finalDecisionDate,
            'siPenaltyImposedType' => $siPenaltyImposedType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'executed' => $executed
        ];

        $filteredImposedErru = [
            'penaltyImposedIdentifier' => 1111,
            'finalDecisionDate' => new \DateTime($finalDecisionDate . ' 00:00:00'),
            'siPenaltyImposedType' => $siPenaltyImposedType,
            'startDate' => new \DateTime($startDate . ' 00:00:00'),
            'endDate' => new \DateTime($endDate . ' 00:00:00'),
            'executed' => $executedRefData
        ];

        //requested erru
        $siPenaltyRequestedType = 301;
        $duration = 12;

        $requestedErru = [
            'penaltyRequestedIdentifier' => 2222,
            'siPenaltyRequestedType' => $siPenaltyRequestedType,
            'duration' => $duration
        ];

        //serious infringement
        $infringementDate = '2015-12-25';
        $siCategoryType = 101;

        $si = [
            'infringementDate' => $infringementDate,
            'siCategoryType' => $siCategoryType,
            'imposedErrus' => [
                0 => $imposedErru
            ],
            'requestedErrus' => [
                0 => $requestedErru
            ]
        ];

        $filteredSi = [
            'infringementDate' => new \DateTime($infringementDate . ' 00:00:00'),
            'siCategoryType' => $siCategoryType,
            'imposedErrus' => [
                0 => $filteredImposedErru
            ],
            'requestedErrus' => [
                0 => $requestedErru
            ]
        ];

        $xmlData = ['array of pre-filtered xml data'];

        $erruData = [
            'workflowId' => $workflowId,
            'memberStateCode' => $memberStateCode,
            'notificationNumber' => $notificationNumber,
            'originatingAuthority' => $originatingAuthority,
            'licenceNumber' => $licenceNumber,
            'communityLicenceNumber' => $communityLicenceNumber,
            'vrm' => $vrm,
            'checkDate' => $checkDate,
            'transportUndertakingName' => $transportUndertakingName,
            'si' => [
                0 => $si
            ]
        ];

        $this->validInitialInput($xmlString, $xmlData, $erruData, new \DOMDocument());
        $this->validSiInput($si, $filteredSi);

        $licenceEntity = m::mock(LicenceEntity::class);
        $licenceEntity->shouldReceive('getId')->times(2)->andReturn($licenceId);
        $licenceEntity->expects('getTotAuthVehicles')->withNoArgs()->andReturn($totAuthVehicles);

        $this->repoMap['Licence']
            ->expects('fetchByLicNoWithoutAdditionalData')
            ->with($licenceNumber)
            ->andReturn($licenceEntity);

        $countryEntity = m::mock(CountryEntity::class);

        $this->repoMap['Country']
            ->expects('fetchById')
            ->with($memberStateCode)
            ->andReturn($countryEntity);

        $siCategoryTypeEntity = m::mock(SiCategoryTypeEntity::class);

        $this->repoMap['SiCategoryType']
            ->expects('fetchById')
            ->with($siCategoryType)
            ->andReturn($siCategoryTypeEntity);

        $siCategoryEntity = m::mock(SiCategoryEntity::class);

        $this->repoMap['SiCategory']
            ->expects('fetchById')
            ->with(SiCategoryEntity::ERRU_DEFAULT_CATEGORY)
            ->andReturn($siCategoryEntity);

        $siPenaltyImposedTypeEntity = m::mock(SiPenaltyImposedTypeEntity::class);

        $this->repoMap['SiPenaltyImposedType']
            ->expects('fetchById')
            ->with($siPenaltyImposedType)
            ->andReturn($siPenaltyImposedTypeEntity);

        $siPenaltyRequestedTypeEntity = m::mock(SiPenaltyRequestedTypeEntity::class);

        $this->repoMap['SiPenaltyRequestedType']
            ->expects('fetchById')
            ->with($siPenaltyRequestedType)
            ->andReturn($siPenaltyRequestedTypeEntity);

        $this->repoMap['ErruRequest']
            ->expects('existsByWorkflowId')
            ->with($workflowId)
            ->andReturn(false);

        $this->repoMap['Cases']->expects('save')->with(m::type(CasesEntity::class));

        $taskResult = new Result();
        $taskResult->addId('task', 88);
        $taskData = [
            'category' => CategoryEntity::CATEGORY_COMPLIANCE,
            'subCategory' => CategoryEntity::TASK_SUB_CATEGORY_NR,
            'description' => 'ERRU case has been automatically created',
            'actionDate' => date('Y-m-d'),
            'urgent' => 'Y',
            'case' => null,
            'licence' => $licenceId,
        ];

        $this->expectedSideEffect(CreateTask::class, $taskData, $taskResult);

        $documentUpdateData = [
            'id' => $documentId,
            'case' => null,
            'licence' => $licenceId,
        ];

        $this->expectedSideEffect(UpdateDocLinksCmd::class, $documentUpdateData, new Result());

        $result = $this->sut->handleCommand($command);
        $this->assertInstanceOf(Result::class, $result);

        $errors = $this->sut->getErrors();
        $this->assertEmpty($errors);
        $this->assertFalse($result->getFlag('hasErrors'));
    }

    /**
     * Tests errors are thrown for erru requests which already exist
     */
    public function testErrorsForDoctrinePenaltyAndCategoryData(): void
    {
        $xmlString = 'xml string';
        $memberStateCode = 'PL';
        $documentId = 111;
        $command = ComplianceEpisodeCmd::create(['id' => $documentId]);

        $this->fetchDocumentAndXml($command, $xmlString, $documentId);

        $licenceNumber = 'OB1234567';
        $communityLicenceNumber = 'UKGB/OB1234567/00001';
        $totAuthVehicles = 10;
        $workflowId = '0ffefb6b-6344-4a60-9a53-4381c32f98d9';
        $siCategoryType = 101;
        $checkDate = '2015-12-24';

        //imposed erru
        $siPenaltyImposedType = 102;
        $executedRefData = 'pen_erru_imposed_executed_yes';

        $filteredImposedErru = [
            'siPenaltyImposedType' => $siPenaltyImposedType,
            'executed' => $executedRefData
        ];

        //requested erru
        $siPenaltyRequestedType = 301;
        $duration = 12;

        $requestedErru = [
            'siPenaltyRequestedType' => $siPenaltyRequestedType,
            'duration' => $duration
        ];

        $si = ['si'];

        $filteredSi = [
            'siCategoryType' => $siCategoryType,
            'imposedErrus' => [
                0 => $filteredImposedErru
            ],
            'requestedErrus' => [
                0 => $requestedErru
            ]
        ];

        $this->validSiInput($si, $filteredSi);

        $this->repoMap['SiCategoryType']
            ->expects('fetchById')
            ->with($siCategoryType)
            ->andThrow(NotFoundException::class);

        $this->repoMap['SiPenaltyImposedType']
            ->expects('fetchById')
            ->with($siPenaltyImposedType)
            ->andThrow(NotFoundException::class);

        $this->repoMap['SiPenaltyRequestedType']
            ->expects('fetchById')
            ->with($siPenaltyRequestedType)
            ->andThrow(NotFoundException::class);

        $xmlData = ['array of pre-filtered xml data'];

        $erruData = [
            'licenceNumber' => $licenceNumber,
            'communityLicenceNumber' => $communityLicenceNumber,
            'checkDate' => $checkDate,
            'workflowId' => $workflowId,
            'memberStateCode' => $memberStateCode,
            'originatingAuthority' => 'originating authority',
            'transportUndertakingName' => 'transport undertaking name',
            'vrm' => 'vrm',
            'notificationNumber' => 'notification number',
            'si' => [
                0 => $si
            ]
        ];

        $this->validInitialInput($xmlString, $xmlData, $erruData, new \DOMDocument());

        $this->repoMap['ErruRequest']
            ->expects('existsByWorkflowId')
            ->with($workflowId)
            ->andReturn(false);

        $this->repoMap['Country']
            ->expects('fetchById')
            ->with($memberStateCode)
            ->andReturn(m::mock(CountryEntity::class));

        $licence = m::mock(LicenceEntity::class);
        $licence->expects('getTotAuthVehicles')->withNoArgs()->andReturn($totAuthVehicles);

        $this->repoMap['Licence']
            ->expects('fetchByLicNoWithoutAdditionalData')
            ->with($licenceNumber)
            ->andReturn($licence);

        $this->handleErrors();

        $result = $this->sut->handleCommand($command);
        $this->assertInstanceOf(Result::class, $result);

        $imposedPenalty = sprintf(ComplianceEpisode::MISSING_IMPOSED_PENALTY_ERROR, $siPenaltyImposedType);
        $requestedPenaltyError = sprintf(ComplianceEpisode::MISSING_REQUESTED_PENALTY_ERROR, $siPenaltyRequestedType);
        $siCategoryTypeError = sprintf(ComplianceEpisode::MISSING_SI_CATEGORY_ERROR, $siCategoryType);
        $errors = $this->sut->getErrors();

        $this->assertCount(3, $errors);
        $this->assertContains($imposedPenalty, $errors);
        $this->assertContains($requestedPenaltyError, $errors);
        $this->assertContains($siCategoryTypeError, $errors);
        $this->assertTrue($result->getFlag('hasErrors'));
    }

    /**
     * tests errors are returned when licence data is missing
     */
    public function testErrorsForMissingLicenceData(): void
    {
        $xmlString = 'xml string';
        $documentId = 111;
        $command = ComplianceEpisodeCmd::create(['id' => $documentId]);

        $this->fetchDocumentAndXml($command, $xmlString, $documentId);

        $licenceNumber = 'OB1234567';
        $workflowId = '0ffefb6b-6344-4a60-9a53-4381c32f98d9';
        $memberStateCode = 'PL';

        $xmlData = ['array of pre-filtered xml data'];

        $erruData = [
            'licenceNumber' => $licenceNumber,
            'workflowId' => $workflowId,
            'memberStateCode' => $memberStateCode,
        ];

        $this->validInitialInput($xmlString, $xmlData, $erruData, new \DOMDocument());

        $licenceError = 'licence not found error';

        $this->repoMap['Licence']
            ->expects('fetchByLicNoWithoutAdditionalData')
            ->with($licenceNumber)
            ->andThrow(NotFoundException::class, $licenceError);

        $this->repoMap['Country']
            ->expects('fetchById')
            ->with($memberStateCode)
            ->andReturn(m::mock(CountryEntity::class));

        $this->repoMap['ErruRequest']
            ->expects('existsByWorkflowId')
            ->with($workflowId)
            ->andReturn(false);

        $this->handleErrors();

        $result = $this->sut->handleCommand($command);
        $this->assertInstanceOf(Result::class, $result);

        $errors = $this->sut->getErrors();

        $this->assertCount(1, $errors);
        $this->assertContains($licenceError, $errors);
        $this->assertTrue($result->getFlag('hasErrors'));
    }

    /**
     * tests errors are returned when licence data is missing
     */
    public function testErrorsForMissingMemberState(): void
    {
        $xmlString = 'xml string';
        $documentId = 111;
        $command = ComplianceEpisodeCmd::create(['id' => $documentId]);

        $this->fetchDocumentAndXml($command, $xmlString, $documentId);

        $licenceNumber = 'OB1234567';
        $workflowId = '0ffefb6b-6344-4a60-9a53-4381c32f98d9';
        $memberStateCode = 'PL';

        $xmlData = ['array of pre-filtered xml data'];

        $erruData = [
            'licenceNumber' => $licenceNumber,
            'workflowId' => $workflowId,
            'memberStateCode' => $memberStateCode,
        ];

        $this->validInitialInput($xmlString, $xmlData, $erruData, new \DOMDocument());

        $this->repoMap['Licence']
            ->expects('fetchByLicNoWithoutAdditionalData')
            ->with($licenceNumber)
            ->andReturn(m::mock(LicenceEntity::class));

        $this->repoMap['Country']
            ->expects('fetchById')
            ->with($memberStateCode)
            ->andThrow(NotFoundException::class);

        $this->repoMap['ErruRequest']
            ->expects('existsByWorkflowId')
            ->with($workflowId)
            ->andReturn(false);

        $this->handleErrors();

        $result = $this->sut->handleCommand($command);
        $this->assertInstanceOf(Result::class, $result);

        $memberStateError = sprintf(ComplianceEpisode::MISSING_MEMBER_STATE_ERROR, $memberStateCode);
        $errors = $this->sut->getErrors();

        $this->assertCount(1, $errors);
        $this->assertContains($memberStateError, $errors);
        $this->assertTrue($result->getFlag('hasErrors'));
    }

    /**
     * Tests errors are thrown for erru requests which already exist
     */
    public function testErrorsForExistingErruRequest(): void
    {
        $xmlString = 'xml string';
        $memberStateCode = 'PL';
        $documentId = 111;
        $command = ComplianceEpisodeCmd::create(['id' => $documentId]);

        $this->fetchDocumentAndXml($command, $xmlString, $documentId);

        $licenceNumber = 'OB1234567';
        $workflowId = '0ffefb6b-6344-4a60-9a53-4381c32f98d9';

        $xmlData = ['array of pre-filtered xml data'];

        $erruData = [
            'licenceNumber' => $licenceNumber,
            'workflowId' => $workflowId,
            'memberStateCode' => $memberStateCode,
        ];

        $this->validInitialInput($xmlString, $xmlData, $erruData, new \DOMDocument());

        $this->repoMap['ErruRequest']
            ->expects('existsByWorkflowId')
            ->with($workflowId)
            ->andReturn(true);

        $this->repoMap['Country']
            ->expects('fetchById')
            ->with($memberStateCode)
            ->andReturn(m::mock(CountryEntity::class));

        $this->repoMap['Licence']
            ->expects('fetchByLicNoWithoutAdditionalData')
            ->with($licenceNumber)
            ->andReturn(m::mock(LicenceEntity::class));

        $this->handleErrors();

        $result = $this->sut->handleCommand($command);
        $this->assertInstanceOf(Result::class, $result);

        $workFlowIdError = sprintf(ComplianceEpisode::WORKFLOW_ID_EXISTS, $workflowId);
        $errors = $this->sut->getErrors();

        $this->assertCount(1, $errors);
        $this->assertContains($workFlowIdError, $errors);
        $this->assertTrue($result->getFlag('hasErrors'));
    }

    /**
     * Tests errors are thrown for erru requests which already exist
     */
    public function testErrorsForSeriousInfringementInputFailure(): void
    {
        $xmlString = 'xml string';
        $memberStateCode = 'PL';
        $documentId = 111;
        $command = ComplianceEpisodeCmd::create(['id' => $documentId]);

        $this->fetchDocumentAndXml($command, $xmlString, $documentId);

        $licenceNumber = 'OB1234567';
        $communityLicenceNumber = 'UKGB/OB1234567/00001';
        $totAuthVehicles = 10;
        $workflowId = '0ffefb6b-6344-4a60-9a53-4381c32f98d9';
        $checkDate = '2015-12-24';
        $si = ['si'];

        $xmlData = ['array of pre-filtered xml data'];

        $erruData = [
            'licenceNumber' => $licenceNumber,
            'communityLicenceNumber' => $communityLicenceNumber,
            'checkDate' => $checkDate,
            'workflowId' => $workflowId,
            'memberStateCode' => $memberStateCode,
            'originatingAuthority' => 'originating authority',
            'transportUndertakingName' => 'transport undertaking name',
            'vrm' => 'vrm',
            'notificationNumber' => 'notification number',
            'si' => [
                0 => $si
            ]
        ];

        $this->validInitialInput($xmlString, $xmlData, $erruData, new \DOMDocument());

        $this->repoMap['ErruRequest']
            ->expects('existsByWorkflowId')
            ->with($workflowId)
            ->andReturn(false);

        $this->repoMap['Country']
            ->expects('fetchById')
            ->with($memberStateCode)
            ->andReturn(m::mock(CountryEntity::class));

        $licence = m::mock(LicenceEntity::class);
        $licence->expects('getTotAuthVehicles')->withNoArgs()->andReturn($totAuthVehicles);

        $this->repoMap['Licence']
            ->expects('fetchByLicNoWithoutAdditionalData')
            ->with($licenceNumber)
            ->andReturn($licence);

        $this->mockedSmServices['SeriousInfringementInput']
            ->expects('setValue')
            ->with($si)
            ->andReturnSelf();

        $this->mockedSmServices['SeriousInfringementInput']
            ->expects('isValid')
            ->with([])
            ->andReturn(false);

        $inputFilterErrors = ['message 1', 'message2'];

        $this->mockedSmServices['SeriousInfringementInput']
            ->shouldReceive('getMessages')
            ->andReturn($inputFilterErrors);

        $this->handleErrors();

        $result = $this->sut->handleCommand($command);
        $this->assertInstanceOf(Result::class, $result);

        $this->assertEquals($inputFilterErrors, $this->sut->getErrors());
        $this->assertTrue($result->getFlag('hasErrors'));
    }

    /**
     * Tests errors in the XML are handled correctly
     */
    public function testErrorsForXmlInputFailure(): void
    {
        $xmlString = 'xml string';
        $documentId = 111;
        $command = ComplianceEpisodeCmd::create(['id' => $documentId]);

        $this->fetchDocumentAndXml($command, $xmlString, $documentId);

        $this->mockedSmServices['ComplianceXmlStructure']
            ->expects('setValue')
            ->with($xmlString)
            ->andReturnSelf();

        $this->mockedSmServices['ComplianceXmlStructure']
            ->expects('isValid')
            ->with([])
            ->andReturn(false);

        $inputFilterErrors = ['message 1', 'message2'];

        $this->mockedSmServices['ComplianceXmlStructure']
            ->expects('getMessages')
            ->andReturn($inputFilterErrors);

        $this->handleErrors();

        $result = $this->sut->handleCommand($command);
        $this->assertInstanceOf(Result::class, $result);

        $this->assertEquals($inputFilterErrors, $this->sut->getErrors());
        $this->assertTrue($result->getFlag('hasErrors'));
    }

    /**
     * Tests errors in the XML are handled correctly
     */
    public function testErrorsForComplianceEpisodeInputFailure(): void
    {
        $xmlString = 'xml string';
        $documentId = 111;
        $command = ComplianceEpisodeCmd::create(['id' => $documentId]);

        $xmlDomDocument = new \DOMDocument();

        $this->fetchDocumentAndXml($command, $xmlString, $documentId);
        $this->validXml($xmlString, $xmlDomDocument);

        $xmlData = ['array of pre-filtered xml data'];

        $this->mapXmlFile($xmlData, $xmlDomDocument);

        $this->mockedSmServices['ComplianceEpisodeInput']
            ->expects('setValue')
            ->with($xmlData)
            ->andReturnSelf();

        $this->mockedSmServices['ComplianceEpisodeInput']
            ->expects('isValid')
            ->with([])
            ->andReturn(false);

        $inputFilterErrors = ['message 1', 'message2'];

        $this->mockedSmServices['ComplianceEpisodeInput']
            ->expects('getMessages')
            ->andReturn($inputFilterErrors);

        $this->handleErrors();

        $result = $this->sut->handleCommand($command);
        $this->assertInstanceOf(Result::class, $result);

        $this->assertEquals($inputFilterErrors, $this->sut->getErrors());
        $this->assertTrue($result->getFlag('hasErrors'));
    }

    /**
     * Gets the email command to send the erru error email
     */
    private function handleErrors(): void
    {
        $erruFailureId = 12345;

        $this->repoMap['ErruRequestFailure']
            ->expects('save')
            ->with(m::type(ErruRequestFailure::class))
            ->andReturnUsing(
                function (ErruRequestFailure $erruRequestFailure) use (&$savedErruRequestFailure) {
                    $erruRequestFailure->setId(12345);
                    $savedErruRequestFailure = $erruRequestFailure;
                }
            );

        $cmdData = ['id' => $erruFailureId];

        $this->expectedEmailQueueSideEffect(SendErruErrorsCmd::class, $cmdData, $erruFailureId, new Result());
    }

    /**
     * Creates assertions for fetching the document and associated xml
     *
     * @param $command
     * @param $xmlString
     * @param $documentId
     * @param int $documentIdTimes
     */
    private function fetchDocumentAndXml(mixed $command, mixed $xmlString, mixed $documentId, int $documentIdTimes = 0): void
    {
        $docIdentifier = 'doc/identifier';

        $documentEntity = m::mock(DocumentEntity::class);
        $documentEntity->shouldReceive('getIdentifier')->once()->andReturn($docIdentifier);
        $documentEntity->shouldReceive('getId')->times($documentIdTimes)->andReturn($documentId);

        $this->repoMap['Document']
            ->expects('fetchUsingId')
            ->with($command)->andReturn($documentEntity);

        $xmlFile = m::mock(File::class);
        $xmlFile->shouldReceive('getContent')->once()->andReturn($xmlString);

        $this->mockedSmServices['FileUploader']
            ->expects('download')
            ->with($docIdentifier)
            ->andReturn($xmlFile);
    }

    /**
     * @param $xmlString
     * @param $erruData
     * @param $xmlDomDocument
     */
    private function validInitialInput(mixed $xmlString, mixed $xmlData, mixed $erruData, mixed $xmlDomDocument): void
    {
        $this->validXml($xmlString, $xmlDomDocument);
        $this->mapXmlFile($xmlData, $xmlDomDocument);
        $this->validComplianceEpisodeInput($erruData, $xmlData);
    }

    /**
     * Creates assertions for a valid set of xml
     *
     * @param $xmlString
     * @param $xmlDomDocument
     */
    private function validXml(mixed $xmlString, mixed $xmlDomDocument): void
    {
        $this->mockedSmServices['ComplianceXmlStructure']
            ->expects('setValue')
            ->with($xmlString)
            ->andReturnSelf();

        $this->mockedSmServices['ComplianceXmlStructure']
            ->expects('isValid')
            ->with([])
            ->andReturn(true);

        $this->mockedSmServices['ComplianceXmlStructure']
            ->expects('getValue')
            ->andReturn($xmlDomDocument);
    }

    /**
     * Creates assertions for a valid compliance episode input
     *
     * @param $erruData
     * @param $xmlData
     */
    private function validComplianceEpisodeInput(mixed $erruData, mixed $xmlData): void
    {
        $this->mockedSmServices['ComplianceEpisodeInput']
            ->expects('setValue')
            ->with($xmlData)
            ->andReturnSelf();

        $this->mockedSmServices['ComplianceEpisodeInput']
            ->expects('isValid')
            ->with([])
            ->andReturn(true);

        $this->mockedSmServices['ComplianceEpisodeInput']
            ->expects('getValue')
            ->andReturn($erruData);
    }

    /**
     * @param $si
     * @param $filteredSi
     */
    private function validSiInput(mixed $si, mixed $filteredSi): void
    {
        $this->mockedSmServices['SeriousInfringementInput']
            ->expects('setValue')
            ->with($si)
            ->andReturnSelf();

        $this->mockedSmServices['SeriousInfringementInput']
            ->expects('isValid')
            ->with([])
            ->andReturn(true);

        $this->mockedSmServices['SeriousInfringementInput']
            ->expects('getValue')
            ->andReturn($filteredSi);
    }

    /**
     * @param $xmlData
     * @param $xmlDomDocument
     */
    private function mapXmlFile(mixed $xmlData, mixed $xmlDomDocument): void
    {
        $this->mockedSmServices['ComplianceEpisodeXmlMapping']
            ->shouldReceive('mapData')
            ->with($xmlDomDocument)
            ->andReturn($xmlData);
    }
}

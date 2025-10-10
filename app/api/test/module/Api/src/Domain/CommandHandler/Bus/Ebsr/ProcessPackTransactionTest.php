<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr\ProcessPackTransaction;
use Dvsa\Olcs\Api\Entity\Bus\BusServiceType as BusServiceTypeEntity;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod as BusNoticePeriodEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\BusRegistrationInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ProcessedDataInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\ShortNoticeInputFactory;
use Dvsa\Olcs\Api\Service\Ebsr\InputFilter\XmlStructureInputFactory;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\ProcessPackTransaction as ProcessPackTransactionCmd;
use Dvsa\Olcs\Api\Service\Ebsr\FileProcessorInterface;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrReceived as SendEbsrReceivedCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRefreshed as SendEbsrRefreshedCmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\CreateTxcInbox as CreateTxcInboxCmd;
use Dvsa\Olcs\Transfer\Command\Document\UpdateDocumentLinks as UpdateDocumentLinksCmd;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\RequestMap as RequestMapQueueCmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\CreateBusFee as CreateBusFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrErrors as SendEbsrErrorsCmd;
use Dvsa\Olcs\Transfer\Command\Document\Upload as UploadCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\EbsrPackException;
use org\bovigo\vfs\vfsStream;

/**
 * @see ProcessPackTransaction
 */
class ProcessPackTransactionTest extends ProcessPackTestCase
{
    public function setUp(): void
    {
        $this->sut = new ProcessPackTransaction();
        parent::setUp();
    }

    /**
     * Tests successful creation of a data refresh through EBSR
     *
     * @param $busShortNotice
     * @param $shortNoticeSetTimes
     *
     * @dataProvider handleDataRefreshProvider
     */
    public function testHandleCommandDataRefresh($busShortNotice, $shortNoticeSetTimes): void
    {
        $filePath = 'vfs://root';
        $xmlName = $filePath . '/xml-file-name.xml';
        $xmlDocument = "<xml></xml>";
        $ebsrSubId = 1234;
        $organisationId = 5678;
        $documentId = 91011;
        $documentDescription = 'document description';
        $licenceId = 121314;
        $variationBusRegId = 151617;
        $submissionTypeId = 'submission type id';
        $organisation = m::mock(OrganisationEntity::class);
        $busRegStatus = BusRegEntity::STATUS_REGISTERED;

        $fileSystem = vfsStream::setup();
        $supportingDocName = 'supporting.doc';
        $supportingDocFilename = vfsStream::url('root/' . $supportingDocName);
        $supportingDocContent = 'doc content';
        $file = vfsStream::newFile($supportingDocName);
        $file->setContent($supportingDocContent);
        $fileSystem->addChild($file);

        $this->supportingDocSideEffect(
            $supportingDocFilename,
            $variationBusRegId,
            $licenceId,
            $supportingDocName,
            'Supporting document'
        );

        $supportingMapName = 'map.doc';
        $supportingMapFilename = vfsStream::url('root/' . $supportingMapName);
        $supportingMapContent = 'map content';
        $file = vfsStream::newFile($supportingMapName);
        $file->setContent($supportingMapContent);
        $fileSystem->addChild($file);

        $this->supportingDocSideEffect(
            $supportingMapFilename,
            $variationBusRegId,
            $licenceId,
            $supportingMapName,
            'Schematic map'
        );

        $busServiceTypes = [
            'type1_key' => 'service type 1',
            'type2_key' => 'service type 2'
        ];

        $naptanCodes = ['naptan codes'];

        $trafficArea1 = m::mock(TrafficAreaEntity::class);
        $trafficArea2 = m::mock(TrafficAreaEntity::class);
        $trafficArea3 = m::mock(TrafficAreaEntity::class);

        $parsedTrafficAreas = ['parsed traffic areas'];

        $parsedLocalAuthorities = ['parsed local authorities'];

        $docIdentifier = 'doc/identifier';
        $document = $this->basicDocument($docIdentifier, $documentDescription);
        $document->shouldReceive('getId')->once()->andReturn($documentId);

        $cmdData = [
            'organisation' => $organisationId,
            'id' => $ebsrSubId
        ];

        $command = ProcessPackTransactionCmd::create($cmdData);

        $xmlDocContext = ['xml_filename' => $xmlName];

        $parsedSchemaVersion = '2.1';
        $parsedLicenceNumber = 'OB1234567';
        $parsedVariationNumber = 666;
        $parsedRouteNumber = '12345';
        $parsedOrganisationEmail = 'foo@bar.com';
        $existingRegNo = 'OB1234567/12345';

        $otherServiceNumber1 = '123';
        $otherServiceNumber2 = '456';
        $mainServiceNumber = 'BUS001';

        $parsedEbsrData = [
            'txcSchemaVersion'  => $parsedSchemaVersion,
            'licNo' => $parsedLicenceNumber,
            'variationNo' => $parsedVariationNumber,
            'routeNo' => $parsedRouteNumber,
            'organisationEmail' => $parsedOrganisationEmail,
            'existingRegNo' => $existingRegNo,
            'subsidised' => 'bs_in_part',
            'busNoticePeriod' => 1,
            'txcAppType' => BusRegEntity::TXC_APP_NON_CHARGEABLE,
            'serviceClassifications' => $busServiceTypes,
            'trafficAreas' => $parsedTrafficAreas,
            'localAuthorities' => $parsedLocalAuthorities,
            'naptan' => $naptanCodes,
            'documents' => [
                0 => $supportingDocName
            ],
            'map' => $supportingMapName,
            'lineNames' => [$mainServiceNumber, $otherServiceNumber1, $otherServiceNumber2],
            'busShortNotice' => $busShortNotice
        ];

        $extraProcessedEbsrData = [
            'subsidised' => $this->refData['bs_in_part'],
            'trafficAreas' => $this->trafficAreas($parsedTrafficAreas, $trafficArea1, $trafficArea2, $trafficArea3),
            'naptanAuthorities' => $this->naptan($naptanCodes),
            'busNoticePeriod' => $this->references[BusNoticePeriodEntity::class][1],
            'localAuthoritys' => $this->localAuthorities($parsedLocalAuthorities, $trafficArea2, $trafficArea3),
            'busServiceTypes' => $this->busServiceTypes($busServiceTypes),
            'serviceNo' => $mainServiceNumber,
            'otherServiceNumbers' => [$otherServiceNumber1, $otherServiceNumber2]
        ];

        $processedDataOutput = array_merge($parsedEbsrData, $extraProcessedEbsrData);
        unset($processedDataOutput['lineNames']); // Remove lineNames as it gets transformed
        $busRegFromData = $processedDataOutput;
        unset($busRegFromData['documents']);
        unset($busRegFromData['variationNo']);

        $ebsrSubmission = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmission->shouldReceive('beginValidating')
            ->once()
            ->with($this->refData[EbsrSubmissionEntity::VALIDATING_STATUS])
            ->andReturnSelf();
        $ebsrSubmission->shouldReceive('getId')->andReturn($ebsrSubId);
        $ebsrSubmission->shouldReceive('getOrganisation')->once()->andReturn($organisation);
        $ebsrSubmission->shouldReceive('getDocument')->twice()->andReturn($document);
        $ebsrSubmission->shouldReceive('getEbsrSubmissionType->getId')->once()->andReturn($submissionTypeId);
        $ebsrSubmission->shouldReceive('setLicenceNo')->with($parsedLicenceNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setVariationNo')->with($parsedVariationNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setRegistrationNo')->with($parsedRouteNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setOrganisationEmailAddress')
            ->with($parsedOrganisationEmail)
            ->once()
            ->andReturnSelf();
        $ebsrSubmission->shouldReceive('setBusReg')
            ->with(m::type(BusRegEntity::class))
            ->once()
            ->andReturnSelf();
        $ebsrSubmission->shouldReceive('setTxcVersion')
            ->once()
            ->with($parsedEbsrData['txcSchemaVersion'])
            ->andReturnSelf();

        $this->ebsrSubmissionRepo($command, $ebsrSubmission, 3);

        $this->mockInput(XmlStructureInputFactory::class, $xmlName, $xmlDocContext, $xmlDocument);

        $busRegInputContext = [
            'submissionType' => $submissionTypeId,
            'organisation' => $organisation
        ];

        $variationBusReg = m::mock(BusRegEntity::class)->makePartial();
        $variationBusReg->shouldReceive('fromData')->once()->with($busRegFromData);
        $variationBusReg->shouldReceive('populateShortNotice')->never();
        $variationBusReg->shouldReceive('setIsShortNotice')->with('Y')->times($shortNoticeSetTimes);
        $variationBusReg->shouldReceive('setOtherServices')->once()->with(m::type(ArrayCollection::class));
        $variationBusReg->shouldReceive('addOtherServiceNumber')->once()->with($otherServiceNumber1);
        $variationBusReg->shouldReceive('addOtherServiceNumber')->once()->with($otherServiceNumber2);
        $variationBusReg->shouldReceive('getId')->times(5)->andReturn($variationBusRegId);
        $variationBusReg->shouldReceive('getLicence->getId')->times(3)->andReturn($licenceId);
        $variationBusReg->shouldReceive('getIsShortNotice')->once()->andReturn('Y');
        $variationBusReg->shouldReceive('getShortNotice->fromData')->once()->with($busShortNotice);
        $variationBusReg->shouldReceive('setEbsrSubmissions')->once()->with(m::type(ArrayCollection::class));
        $variationBusReg->shouldReceive('isEbsrRefresh')->once()->andReturn(true);
        $variationBusReg->shouldReceive('getStatus->getId')->once()->andReturn($busRegStatus);

        $previousBusRegNoExclusions = m::mock(BusRegEntity::class);
        $previousBusReg = m::mock(BusRegEntity::class);
        $previousBusReg->shouldReceive('createVariation')
            ->with($this->refData[$busRegStatus], $this->refData[$busRegStatus])
            ->once()
            ->andReturn($variationBusReg);

        $this->repoMap['Bus']->shouldReceive('save')
            ->once()
            ->with($variationBusReg)
            ->andReturn($variationBusReg);

        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('getLatestBusVariation')->once()->with($existingRegNo)->andReturn($previousBusReg);
        $licence->expects('getLatestBusVariation')->with($existingRegNo, [])->andReturn($previousBusRegNoExclusions);

        $this->repoMap['Licence']->shouldReceive('fetchByLicNoWithoutAdditionalData')
            ->once()
            ->with($parsedLicenceNumber)
            ->andReturn($licence);

        $this->mockInput(BusRegistrationInputFactory::class, $xmlDocument, $busRegInputContext, $parsedEbsrData);

        $this->fileProcessor($docIdentifier, $xmlName);

        $processedContext = [
            'busReg' => $previousBusReg,
            'busRegNoExclusions' => $previousBusRegNoExclusions,
        ];

        //this is just validators for the data generated by doctrine, no data is filtered
        $this->mockInput(ProcessedDataInputFactory::class, $processedDataOutput, $processedContext, $processedDataOutput);

        $shortNoticeContext = ['busReg' => $variationBusReg];

        //this is just validators for short notice information, no data is filtered
        $this->mockInput(ShortNoticeInputFactory::class, $processedDataOutput, $shortNoticeContext, $processedDataOutput);

        $ebsrSubmission->shouldReceive('finishValidating')
            ->once()
            ->with($this->refData[EbsrSubmissionEntity::PROCESSING_STATUS], ['an_array'])
            ->andReturnSelf();

        $ebsrSubmission->shouldReceive('finishProcessing')
            ->once()
            ->with($this->refData[EbsrSubmissionEntity::PROCESSED_STATUS])
            ->andReturnSelf();

        $this->expectedEmailQueueSideEffect(
            SendEbsrRefreshedCmd::class,
            ['id' => $ebsrSubId],
            $ebsrSubId,
            new Result()
        );

        $this->successSideEffects($variationBusRegId, $licenceId, $documentId, false);

        $this->sut->setConfig($this->config);

        $this->sut->handleCommand($command);
    }

    /**
     * @return array
     */
    public function handleDataRefreshProvider(): array
    {
        return [
            [['short notice section'], 1],
            [[], 0]
        ];
    }


    /**
     * Tests successful creation of a variation application through EBSR
     *
     * @param $txcAppType
     * @param $busRegStatus
     * @param $fee
     *
     * @dataProvider handleVariationProvider
     */
    public function testHandleCommandVariation($txcAppType, $busRegStatus, $fee): void
    {
        $filePath = 'vfs://root';
        $xmlName = $filePath . '/xml-file-name.xml';
        $xmlDocument = "<xml></xml>";
        $ebsrSubId = 1234;
        $organisationId = 5678;
        $documentId = 91011;
        $documentDescription = 'document description';
        $licenceId = 121314;
        $variationBusRegId = 151617;
        $submissionTypeId = 'submission type id';
        $organisation = m::mock(OrganisationEntity::class);

        $fileSystem = vfsStream::setup();
        $supportingDocName = 'supporting.doc';
        $supportingDocFilename = vfsStream::url('root/' . $supportingDocName);
        $supportingDocContent = 'doc content';
        $file = vfsStream::newFile($supportingDocName);
        $file->setContent($supportingDocContent);
        $fileSystem->addChild($file);

        $this->supportingDocSideEffect(
            $supportingDocFilename,
            $variationBusRegId,
            $licenceId,
            $supportingDocName,
            'Supporting document'
        );

        $supportingMapName = 'map.doc';
        $supportingMapFilename = vfsStream::url('root/' . $supportingMapName);
        $supportingMapContent = 'map content';
        $file = vfsStream::newFile($supportingMapName);
        $file->setContent($supportingMapContent);
        $fileSystem->addChild($file);

        $this->supportingDocSideEffect(
            $supportingMapFilename,
            $variationBusRegId,
            $licenceId,
            $supportingMapName,
            'Schematic map'
        );

        $busServiceTypes = [
            'type1_key' => 'service type 1',
            'type2_key' => 'service type 2'
        ];

        $naptanCodes = ['naptan codes'];

        $trafficArea1 = m::mock(TrafficAreaEntity::class);
        $trafficArea2 = m::mock(TrafficAreaEntity::class);
        $trafficArea3 = m::mock(TrafficAreaEntity::class);

        $parsedTrafficAreas = ['parsed traffic areas'];

        $parsedLocalAuthorities = ['parsed local authorities'];

        $docIdentifier = 'doc/identifier';
        $document = $this->basicDocument($docIdentifier, $documentDescription);
        $document->shouldReceive('getId')->once()->andReturn($documentId);

        $cmdData = [
            'organisation' => $organisationId,
            'id' => $ebsrSubId
        ];

        $command = ProcessPackTransactionCmd::create($cmdData);

        $xmlDocContext = ['xml_filename' => $xmlName];

        $parsedSchemaVersion = '2.1';
        $parsedLicenceNumber = 'OB1234567';
        $parsedVariationNumber = 666;
        $parsedRouteNumber = '12345';
        $parsedOrganisationEmail = 'foo@bar.com';
        $existingRegNo = 'OB1234567/12345';

        $otherServiceNumber1 = '123';
        $otherServiceNumber2 = '456';
        $mainServiceNumber = 'BUS002';

        $busShortNotice = ['bus short notice'];

        $parsedEbsrData = [
            'txcSchemaVersion'  => $parsedSchemaVersion,
            'licNo' => $parsedLicenceNumber,
            'variationNo' => $parsedVariationNumber,
            'routeNo' => $parsedRouteNumber,
            'organisationEmail' => $parsedOrganisationEmail,
            'existingRegNo' => $existingRegNo,
            'subsidised' => 'bs_in_part',
            'busNoticePeriod' => 1,
            'txcAppType' => $txcAppType,
            'serviceClassifications' => $busServiceTypes,
            'trafficAreas' => $parsedTrafficAreas,
            'localAuthorities' => $parsedLocalAuthorities,
            'naptan' => $naptanCodes,
            'documents' => [
                0 => $supportingDocName
            ],
            'map' => $supportingMapName,
            'lineNames' => [$mainServiceNumber, $otherServiceNumber1, $otherServiceNumber2],
            'busShortNotice' => $busShortNotice
        ];

        $extraProcessedEbsrData = [
            'subsidised' => $this->refData['bs_in_part'],
            'trafficAreas' => $this->trafficAreas($parsedTrafficAreas, $trafficArea1, $trafficArea2, $trafficArea3),
            'naptanAuthorities' => $this->naptan($naptanCodes),
            'busNoticePeriod' => $this->references[BusNoticePeriodEntity::class][1],
            'localAuthoritys' => $this->localAuthorities($parsedLocalAuthorities, $trafficArea2, $trafficArea3),
            'busServiceTypes' => $this->busServiceTypes($busServiceTypes),
            'serviceNo' => $mainServiceNumber,
            'otherServiceNumbers' => [$otherServiceNumber1, $otherServiceNumber2]
        ];

        $processedDataOutput = array_merge($parsedEbsrData, $extraProcessedEbsrData);
        unset($processedDataOutput['lineNames']); // Remove lineNames as it gets transformed
        $busRegFromData = $processedDataOutput;
        unset($busRegFromData['documents']);
        unset($busRegFromData['variationNo']);

        $ebsrSubmission = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmission->shouldReceive('beginValidating')
            ->once()
            ->with($this->refData[EbsrSubmissionEntity::VALIDATING_STATUS])
            ->andReturnSelf();
        $ebsrSubmission->shouldReceive('getId')->andReturn($ebsrSubId);
        $ebsrSubmission->shouldReceive('getOrganisation')->once()->andReturn($organisation);
        $ebsrSubmission->shouldReceive('getDocument')->twice()->andReturn($document);
        $ebsrSubmission->shouldReceive('getEbsrSubmissionType->getId')->once()->andReturn($submissionTypeId);
        $ebsrSubmission->shouldReceive('setLicenceNo')->with($parsedLicenceNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setVariationNo')->with($parsedVariationNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setRegistrationNo')->with($parsedRouteNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setOrganisationEmailAddress')
            ->with($parsedOrganisationEmail)
            ->once()
            ->andReturnSelf();
        $ebsrSubmission->shouldReceive('setBusReg')
            ->with(m::type(BusRegEntity::class))
            ->once()
            ->andReturnSelf();

        $ebsrSubmission->shouldReceive('setTxcVersion')
            ->once()
            ->with($parsedEbsrData['txcSchemaVersion'])
            ->andReturnSelf();

        $this->ebsrSubmissionRepo($command, $ebsrSubmission, 3);

        $this->mockInput(XmlStructureInputFactory::class, $xmlName, $xmlDocContext, $xmlDocument);

        $busRegInputContext = [
            'submissionType' => $submissionTypeId,
            'organisation' => $organisation
        ];

        $variationBusReg = m::mock(BusRegEntity::class)->makePartial();
        $variationBusReg->shouldReceive('fromData')->once()->with($busRegFromData);
        $variationBusReg->shouldReceive('populateShortNotice')->once();
        $variationBusReg->shouldReceive('setOtherServices')->once()->with(m::type(ArrayCollection::class));
        $variationBusReg->shouldReceive('addOtherServiceNumber')->once()->with($otherServiceNumber1);
        $variationBusReg->shouldReceive('addOtherServiceNumber')->once()->with($otherServiceNumber2);
        $variationBusReg->shouldReceive('getId')->times(5)->andReturn($variationBusRegId);
        $variationBusReg->shouldReceive('getLicence->getId')->times(3)->andReturn($licenceId);
        $variationBusReg->shouldReceive('getIsShortNotice')->once()->andReturn('Y');
        $variationBusReg->shouldReceive('getShortNotice->fromData')->once()->with($busShortNotice);
        $variationBusReg->shouldReceive('setEbsrSubmissions')->once()->with(m::type(ArrayCollection::class));
        $variationBusReg->shouldReceive('isEbsrRefresh')->once()->andReturn(false);
        $variationBusReg->shouldReceive('getStatus->getId')->once()->andReturn($busRegStatus);

        $previousBusRegNoExclusions = m::mock(BusRegEntity::class);
        $previousBusReg = m::mock(BusRegEntity::class);
        $previousBusReg->shouldReceive('createVariation')
            ->with($this->refData[$busRegStatus], $this->refData[$busRegStatus])
            ->once()
            ->andReturn($variationBusReg);

        $this->repoMap['Bus']->shouldReceive('save')
            ->once()
            ->with($variationBusReg)
            ->andReturn($variationBusReg);

        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('getLatestBusVariation')->once()->with($existingRegNo)->andReturn($previousBusReg);
        $licence->expects('getLatestBusVariation')->with($existingRegNo, [])->andReturn($previousBusRegNoExclusions);

        $this->repoMap['Licence']->shouldReceive('fetchByLicNoWithoutAdditionalData')
            ->once()
            ->with($parsedLicenceNumber)
            ->andReturn($licence);

        $this->mockInput(BusRegistrationInputFactory::class, $xmlDocument, $busRegInputContext, $parsedEbsrData);

        $this->fileProcessor($docIdentifier, $xmlName);

        $processedContext = [
            'busReg' => $previousBusReg,
            'busRegNoExclusions' => $previousBusRegNoExclusions,
        ];

        //this is just validators for the data generated by doctrine, no data is filtered
        $this->mockInput(ProcessedDataInputFactory::class, $processedDataOutput, $processedContext, $processedDataOutput);

        $shortNoticeContext = ['busReg' => $variationBusReg];

        //this is just validators for short notice information, no data is filtered
        $this->mockInput(ShortNoticeInputFactory::class, $processedDataOutput, $shortNoticeContext, $processedDataOutput);

        $ebsrSubmission->shouldReceive('finishValidating')
            ->once()
            ->with($this->refData[EbsrSubmissionEntity::PROCESSING_STATUS], ['an_array'])
            ->andReturnSelf();

        $ebsrSubmission->shouldReceive('finishProcessing')
            ->once()
            ->with($this->refData[EbsrSubmissionEntity::PROCESSED_STATUS])
            ->andReturnSelf();

        $this->expectedEmailQueueSideEffect(SendEbsrReceivedCmd::class, ['id' => $ebsrSubId], $ebsrSubId, new Result());

        $this->successSideEffects($variationBusRegId, $licenceId, $documentId, $fee);

        $this->sut->setConfig($this->config);

        $this->sut->handleCommand($command);
    }

    /**
     * @return array
     */
    public function handleVariationProvider(): array
    {
        return [
            [BusRegEntity::TXC_APP_CANCEL, BusRegEntity::STATUS_CANCEL, false],
            [BusRegEntity::TXC_APP_CHARGEABLE, BusRegEntity::STATUS_VAR, true]
        ];
    }

    /**
     * Tests successful creation of a new bus reg application through EBSR
     */
    public function testHandleCommandNewApplication(): void
    {
        $xmlName = 'tmp/directory/path/xml-file-name.xml';
        $xmlDocument = "<xml></xml>";
        $ebsrSubId = 1234;
        $organisationId = 5678;
        $documentId = 91011;
        $documentDescription = 'document description';
        $licenceId = 121314;
        $savedBusRegId = 151617;
        $submissionTypeId = 'submission type id';
        $organisation = m::mock(OrganisationEntity::class);

        $busServiceTypes = [
            'type1_key' => 'service type 1',
            'type2_key' => 'service type 2'
        ];

        $naptanCodes = ['naptan codes'];

        $trafficArea1 = m::mock(TrafficAreaEntity::class);
        $trafficArea2 = m::mock(TrafficAreaEntity::class);
        $trafficArea3 = m::mock(TrafficAreaEntity::class);

        $parsedTrafficAreas = ['parsed traffic areas'];

        $parsedLocalAuthorities = ['parsed local authorities'];

        $docIdentifier = 'doc/identifier';
        $document = $this->basicDocument($docIdentifier, $documentDescription);
        $document->shouldReceive('getId')->once()->andReturn($documentId);

        $cmdData = [
            'organisation' => $organisationId,
            'id' => $ebsrSubId
        ];

        $command = ProcessPackTransactionCmd::create($cmdData);

        $xmlDocContext = ['xml_filename' => $xmlName];

        $parsedSchemaVersion = '2.1';
        $parsedLicenceNumber = 'OB1234567';
        $parsedVariationNumber = 666;
        $parsedRouteNumber = '12345';
        $parsedOrganisationEmail = 'foo@bar.com';
        $existingRegNo = 'OB1234567/12345';

        $parsedEbsrData = [
            'txcSchemaVersion'  => $parsedSchemaVersion,
            'licNo' => $parsedLicenceNumber,
            'variationNo' => $parsedVariationNumber,
            'routeNo' => $parsedRouteNumber,
            'organisationEmail' => $parsedOrganisationEmail,
            'existingRegNo' => $existingRegNo,
            'subsidised' => 'bs_in_part',
            'busNoticePeriod' => 1,
            'txcAppType' => BusRegEntity::TXC_APP_NEW,
            'serviceClassifications' => $busServiceTypes,
            'trafficAreas' => $parsedTrafficAreas,
            'localAuthorities' => $parsedLocalAuthorities,
            'naptan' => $naptanCodes,
            'documents' => [

            ],
            'lineNames' => ['BUS003', '123', '456']
        ];

        $ebsrSubmission = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmission->shouldReceive('beginValidating')
            ->once()
            ->with($this->refData[EbsrSubmissionEntity::VALIDATING_STATUS])
            ->andReturnSelf();
        $ebsrSubmission->shouldReceive('getId')->andReturn($ebsrSubId);
        $ebsrSubmission->shouldReceive('getOrganisation')->once()->andReturn($organisation);
        $ebsrSubmission->shouldReceive('getDocument')->twice()->andReturn($document);
        $ebsrSubmission->shouldReceive('getEbsrSubmissionType->getId')->once()->andReturn($submissionTypeId);
        $ebsrSubmission->shouldReceive('setLicenceNo')->with($parsedLicenceNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setVariationNo')->with($parsedVariationNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setRegistrationNo')->with($parsedRouteNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('isDataRefresh')->once()->andReturn(false);
        $ebsrSubmission->shouldReceive('setOrganisationEmailAddress')
            ->with($parsedOrganisationEmail)
            ->once()
            ->andReturnSelf();
        $ebsrSubmission->shouldReceive('setBusReg')
            ->with(m::type(BusRegEntity::class))
            ->once()
            ->andReturnSelf();

        $ebsrSubmission->shouldReceive('setTxcVersion')
            ->once()
            ->with($parsedEbsrData['txcSchemaVersion'])
            ->andReturnSelf();

        $this->ebsrSubmissionRepo($command, $ebsrSubmission, 3);

        $this->repoMap['Bus']->shouldReceive('save')
            ->once()
            ->with(m::type(BusRegEntity::class))
            ->andReturnUsing(
                function (BusRegEntity $busReg) use (&$savedBusReg) {
                    $busReg->setId(151617);
                    $savedBusReg = $busReg;
                }
            );

        $this->mockInput(XmlStructureInputFactory::class, $xmlName, $xmlDocContext, $xmlDocument);

        $busRegInputContext = [
            'submissionType' => $submissionTypeId,
            'organisation' => $organisation
        ];

        $this->mockInput(BusRegistrationInputFactory::class, $xmlDocument, $busRegInputContext, $parsedEbsrData);

        $this->fileProcessor($docIdentifier, $xmlName);

        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('getLicNo')->twice()->andReturn($parsedLicenceNumber);
        $licence->shouldReceive('getLatestBusRouteNo')->once()->andReturn(12345);
        $licence->shouldReceive('getId')->once()->andReturn($licenceId);
        $licence->shouldReceive('getLatestBusVariation')->once()->with($existingRegNo)->andReturnNull();
        $licence->expects('getLatestBusVariation')->with($existingRegNo, [])->andReturnNull();

        $this->repoMap['Licence']->shouldReceive('fetchByLicNoWithoutAdditionalData')
            ->once()
            ->with($parsedLicenceNumber)
            ->andReturn($licence);

        $extraProcessedEbsrData = [
            'subsidised' => $this->refData['bs_in_part'],
            'trafficAreas' => $this->trafficAreas($parsedTrafficAreas, $trafficArea1, $trafficArea2, $trafficArea3),
            'naptanAuthorities' => $this->naptan($naptanCodes),
            'busNoticePeriod' => $this->references[BusNoticePeriodEntity::class][1],
            'localAuthoritys' => $this->localAuthorities($parsedLocalAuthorities, $trafficArea2, $trafficArea3),
            'busServiceTypes' => $this->busServiceTypes($busServiceTypes),
            'serviceNo' => 'BUS003',
            'otherServiceNumbers' => ['123', '456']
        ];

        $processedDataOutput = array_merge($parsedEbsrData, $extraProcessedEbsrData);
        unset($processedDataOutput['lineNames']); // Remove lineNames as it gets transformed
        $processedContext = [
            'busReg' => null,
            'busRegNoExclusions' => null,
        ];

        //this is just validators for the data generated by doctrine, no data is filtered
        $this->mockInput(ProcessedDataInputFactory::class, $processedDataOutput, $processedContext, $processedDataOutput);

        //this is just validators for short notice information, no data is filtered
        $this->mockInput(ShortNoticeInputFactory::class, $processedDataOutput, m::type('array'), $processedDataOutput);

        $ebsrSubmission->shouldReceive('finishValidating')
            ->once()
            ->with($this->refData[EbsrSubmissionEntity::PROCESSING_STATUS], ['an_array'])
            ->andReturnSelf();

        $ebsrSubmission->shouldReceive('finishProcessing')
            ->once()
            ->with($this->refData[EbsrSubmissionEntity::PROCESSED_STATUS])
            ->andReturnSelf();

        $this->expectedEmailQueueSideEffect(SendEbsrReceivedCmd::class, ['id' => $ebsrSubId], $ebsrSubId, new Result());

        $this->successSideEffects(
            $savedBusRegId,
            $licenceId,
            $documentId,
            true
        );

        $this->sut->handleCommand($command);
    }

    /**
     * Tests exception thrown for missing config
     */
    public function testMissingConfigException(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->sut->setConfig([]);

        $ebsrSubId = 1234;
        $organisationId = 5678;

        $cmdData = [
            'organisation' => $organisationId,
            'id' => $ebsrSubId
        ];

        $command = ProcessPackTransactionCmd::create($cmdData);

        $ebsrSubmission = m::mock(EbsrSubmissionEntity::class);
        $ebsrSubmission->shouldReceive('beginValidating')
            ->once()
            ->with($this->refData[EbsrSubmissionEntity::VALIDATING_STATUS])
            ->andReturnSelf();

        $this->ebsrSubmissionRepo($command, $ebsrSubmission, 1);

        $this->sut->handleCommand($command);
    }

    /**
     * Tests document retrieval failure
     */
    public function testFailedDocumentRetrieval(): void
    {
        $ebsrSubId = 1234;
        $organisationId = 5678;
        $organisation = m::mock(OrganisationEntity::class);

        $docIdentifier = 'doc/identifier';
        $documentDescription = 'document description';
        $document = $this->basicDocument($docIdentifier, $documentDescription);

        $cmdData = [
            'organisation' => $organisationId,
            'id' => $ebsrSubId
        ];

        $command = ProcessPackTransactionCmd::create($cmdData);

        $ebsrSubmission = $this->failedEbsrSubmission($ebsrSubId, $organisation, $document);

        $this->ebsrSubmissionRepo($command, $ebsrSubmission, 2);

        $this->mockedSmServices[FileProcessorInterface::class]
            ->shouldReceive('setSubDirPath')
            ->with('root')
            ->once();
        $this->mockedSmServices[FileProcessorInterface::class]
            ->shouldReceive('fetchXmlFileNameFromDocumentStore')
            ->with($docIdentifier)
            ->once()
            ->andThrow(EbsrPackException::class, 'message');

        $this->expectedEmailQueueSideEffect(SendEbsrErrorsCmd::class, ['id' => $ebsrSubId], $ebsrSubId, new Result());
        $this->sut->handleCommand($command);
    }

    /**
     * Tests failure related to xml structure
     */
    public function testFailedXmlStructure(): void
    {
        $xmlName = 'tmp/directory/path/xml-file-name.xml';
        $ebsrSubId = 1234;
        $organisationId = 5678;
        $documentDescription = 'document description';
        $organisation = m::mock(OrganisationEntity::class);

        $docIdentifier = 'doc/identifier';
        $document = $this->basicDocument($docIdentifier, $documentDescription);

        $cmdData = [
            'organisation' => $organisationId,
            'id' => $ebsrSubId
        ];

        $command = ProcessPackTransactionCmd::create($cmdData);

        $xmlDocContext = ['xml_filename' => $xmlName];

        $ebsrSubmission = $this->failedEbsrSubmission($ebsrSubId, $organisation, $document);

        $this->ebsrSubmissionRepo($command, $ebsrSubmission, 2);

        $this->mockInputFailure(XmlStructureInputFactory::class, $xmlName, $xmlDocContext, ['message']);

        $this->fileProcessor($docIdentifier, $xmlName);

        $this->expectedEmailQueueSideEffect(SendEbsrErrorsCmd::class, ['id' => $ebsrSubId], $ebsrSubId, new Result());
        $this->sut->handleCommand($command);
    }

    /**
     * Tests a failure from bus reg input filter
     */
    public function testFailedBusRegInput(): void
    {
        $xmlName = 'tmp/directory/path/xml-file-name.xml';
        $xmlDocument = "<xml></xml>";
        $ebsrSubId = 1234;
        $organisationId = 5678;
        $documentDescription = 'document description';
        $submissionTypeId = 'submission type id';
        $organisation = m::mock(OrganisationEntity::class);

        $docIdentifier = 'doc/identifier';
        $document = $this->basicDocument($docIdentifier, $documentDescription);

        $cmdData = [
            'organisation' => $organisationId,
            'id' => $ebsrSubId
        ];

        $command = ProcessPackTransactionCmd::create($cmdData);

        $xmlDocContext = ['xml_filename' => $xmlName];

        $ebsrSubmission = $this->failedEbsrSubmission($ebsrSubId, $organisation, $document);
        $ebsrSubmission->shouldReceive('getEbsrSubmissionType->getId')->once()->andReturn($submissionTypeId);

        $this->ebsrSubmissionRepo($command, $ebsrSubmission, 2);

        $this->mockInput(XmlStructureInputFactory::class, $xmlName, $xmlDocContext, $xmlDocument);

        $busRegInputContext = [
            'submissionType' => $submissionTypeId,
            'organisation' => $organisation
        ];

        $this->mockInputFailure(BusRegistrationInputFactory::class, $xmlDocument, $busRegInputContext, ['messages']);

        $this->fileProcessor($docIdentifier, $xmlName);

        $this->expectedEmailQueueSideEffect(SendEbsrErrorsCmd::class, ['id' => $ebsrSubId], $ebsrSubId, new Result());
        $this->sut->handleCommand($command);
    }

    /**
     * Tests a failure from processed data input filter
     */
    public function testFailedProcessDataInput(): void
    {
        $xmlName = 'tmp/directory/path/xml-file-name.xml';
        $xmlDocument = "<xml></xml>";
        $ebsrSubId = 1234;
        $organisationId = 5678;
        $documentDescription = 'document description';
        $submissionTypeId = 'submission type id';
        $organisation = m::mock(OrganisationEntity::class);

        $busServiceTypes = [
            'type1_key' => 'service type 1',
            'type2_key' => 'service type 2'
        ];

        $naptanCodes = ['naptan codes'];

        $trafficArea1 = m::mock(TrafficAreaEntity::class);
        $trafficArea2 = m::mock(TrafficAreaEntity::class);
        $trafficArea3 = m::mock(TrafficAreaEntity::class);

        $parsedTrafficAreas = ['parsed traffic areas'];

        $parsedLocalAuthorities = ['parsed local authorities'];

        $docIdentifier = 'doc/identifier';
        $document = $this->basicDocument($docIdentifier, $documentDescription);

        $cmdData = [
            'organisation' => $organisationId,
            'id' => $ebsrSubId
        ];

        $command = ProcessPackTransactionCmd::create($cmdData);

        $xmlDocContext = ['xml_filename' => $xmlName];

        $parsedSchemaVersion = '2.1';
        $parsedLicenceNumber = 'OB1234567';
        $parsedVariationNumber = 666;
        $parsedRouteNumber = '12345';
        $parsedOrganisationEmail = 'foo@bar.com';
        $existingRegNo = 'OB1234567/12345';

        $parsedEbsrData = [
            'txcSchemaVersion'  => $parsedSchemaVersion,
            'licNo' => $parsedLicenceNumber,
            'variationNo' => $parsedVariationNumber,
            'routeNo' => $parsedRouteNumber,
            'organisationEmail' => $parsedOrganisationEmail,
            'existingRegNo' => $existingRegNo,
            'subsidised' => 'bs_in_part',
            'busNoticePeriod' => 1,
            'txcAppType' => BusRegEntity::TXC_APP_NEW,
            'serviceClassifications' => $busServiceTypes,
            'trafficAreas' => $parsedTrafficAreas,
            'localAuthorities' => $parsedLocalAuthorities,
            'naptan' => $naptanCodes,
            'documents' => [

            ],
            'lineNames' => ['BUS004', '123', '456']
        ];

        $ebsrSubmission = $this->failedEbsrSubmission($ebsrSubId, $organisation, $document);
        $ebsrSubmission->shouldReceive('getEbsrSubmissionType->getId')->once()->andReturn($submissionTypeId);
        $ebsrSubmission->shouldReceive('setLicenceNo')->with($parsedLicenceNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setVariationNo')->with($parsedVariationNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setRegistrationNo')->with($parsedRouteNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setOrganisationEmailAddress')
            ->with($parsedOrganisationEmail)
            ->once()
            ->andReturnSelf();

        $ebsrSubmission->shouldReceive('setTxcVersion')
            ->once()
            ->with($parsedEbsrData['txcSchemaVersion'])
            ->andReturnSelf();

        $this->ebsrSubmissionRepo($command, $ebsrSubmission, 2);

        $this->mockInput(XmlStructureInputFactory::class, $xmlName, $xmlDocContext, $xmlDocument);

        $busRegInputContext = [
            'submissionType' => $submissionTypeId,
            'organisation' => $organisation
        ];

        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('getLatestBusVariation')->once()->with($existingRegNo)->andReturnNull();
        $licence->expects('getLatestBusVariation')->with($existingRegNo, [])->andReturnNull();

        $this->repoMap['Licence']->shouldReceive('fetchByLicNoWithoutAdditionalData')
            ->once()
            ->with($parsedLicenceNumber)
            ->andReturn($licence);

        $this->mockInput(BusRegistrationInputFactory::class, $xmlDocument, $busRegInputContext, $parsedEbsrData);

        $extraProcessedEbsrData = [
            'subsidised' => $this->refData['bs_in_part'],
            'trafficAreas' => $this->trafficAreas($parsedTrafficAreas, $trafficArea1, $trafficArea2, $trafficArea3),
            'naptanAuthorities' => $this->naptan($naptanCodes),
            'busNoticePeriod' => $this->references[BusNoticePeriodEntity::class][1],
            'localAuthoritys' => $this->localAuthorities($parsedLocalAuthorities, $trafficArea2, $trafficArea3),
            'busServiceTypes' => $this->busServiceTypes($busServiceTypes),
            'serviceNo' => 'BUS004',
            'otherServiceNumbers' => ['123', '456']
        ];

        $processedDataInput = array_merge($parsedEbsrData, $extraProcessedEbsrData);
        unset($processedDataInput['lineNames']); // Remove lineNames as it gets transformed
        $processedContext = [
            'busReg' => null,
            'busRegNoExclusions' => null,
        ];

        $this->mockInputFailure(ProcessedDataInputFactory::class, $processedDataInput, $processedContext, ['messages']);

        $this->fileProcessor($docIdentifier, $xmlName);

        $this->expectedEmailQueueSideEffect(SendEbsrErrorsCmd::class, ['id' => $ebsrSubId], $ebsrSubId, new Result());
        $this->sut->handleCommand($command);
    }

    /**
     * Tests a failure from processed data input filter
     */
    public function testFailedShortNoticeInput(): void
    {
        $xmlName = 'tmp/directory/path/xml-file-name.xml';
        $xmlDocument = "<xml></xml>";
        $ebsrSubId = 1234;
        $organisationId = 5678;
        $documentDescription = 'document description';
        $submissionTypeId = 'submission type id';
        $organisation = m::mock(OrganisationEntity::class);

        $busServiceTypes = [
            'type1_key' => 'service type 1',
            'type2_key' => 'service type 2'
        ];

        $naptanCodes = ['naptan codes'];

        $trafficArea1 = m::mock(TrafficAreaEntity::class);
        $trafficArea2 = m::mock(TrafficAreaEntity::class);
        $trafficArea3 = m::mock(TrafficAreaEntity::class);

        $parsedTrafficAreas = ['parsed traffic areas'];

        $parsedLocalAuthorities = ['parsed local authorities'];

        $docIdentifier = 'doc/identifier';
        $document = $this->basicDocument($docIdentifier, $documentDescription);

        $cmdData = [
            'organisation' => $organisationId,
            'id' => $ebsrSubId
        ];

        $command = ProcessPackTransactionCmd::create($cmdData);

        $xmlDocContext = ['xml_filename' => $xmlName];

        $parsedSchemaVersion = '2.1';
        $parsedLicenceNumber = 'OB1234567';
        $parsedVariationNumber = 666;
        $parsedRouteNumber = '12345';
        $parsedOrganisationEmail = 'foo@bar.com';
        $existingRegNo = 'OB1234567/12345';

        $parsedEbsrData = [
            'txcSchemaVersion'  => $parsedSchemaVersion,
            'licNo' => $parsedLicenceNumber,
            'variationNo' => $parsedVariationNumber,
            'routeNo' => $parsedRouteNumber,
            'organisationEmail' => $parsedOrganisationEmail,
            'existingRegNo' => $existingRegNo,
            'subsidised' => 'bs_in_part',
            'busNoticePeriod' => 1,
            'txcAppType' => BusRegEntity::TXC_APP_NEW,
            'serviceClassifications' => $busServiceTypes,
            'trafficAreas' => $parsedTrafficAreas,
            'localAuthorities' => $parsedLocalAuthorities,
            'naptan' => $naptanCodes,
            'documents' => [

            ],
            'lineNames' => ['BUS005', '123', '456']
        ];

        $ebsrSubmission = $this->failedEbsrSubmission($ebsrSubId, $organisation, $document);
        $ebsrSubmission->shouldReceive('getEbsrSubmissionType->getId')->once()->andReturn($submissionTypeId);
        $ebsrSubmission->shouldReceive('setLicenceNo')->with($parsedLicenceNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setVariationNo')->with($parsedVariationNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setRegistrationNo')->with($parsedRouteNumber)->once()->andReturnSelf();
        $ebsrSubmission->shouldReceive('setOrganisationEmailAddress')
            ->with($parsedOrganisationEmail)
            ->once()
            ->andReturnSelf();

        $ebsrSubmission->shouldReceive('setTxcVersion')
            ->once()
            ->with($parsedEbsrData['txcSchemaVersion'])
            ->andReturnSelf();

        $this->ebsrSubmissionRepo($command, $ebsrSubmission, 2);

        $this->mockInput(XmlStructureInputFactory::class, $xmlName, $xmlDocContext, $xmlDocument);

        $busRegInputContext = [
            'submissionType' => $submissionTypeId,
            'organisation' => $organisation
        ];

        $this->mockInput(BusRegistrationInputFactory::class, $xmlDocument, $busRegInputContext, $parsedEbsrData);

        $this->fileProcessor($docIdentifier, $xmlName);

        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('getLicNo')->twice()->andReturn($parsedLicenceNumber);
        $licence->shouldReceive('getLatestBusRouteNo')->once()->andReturn(12345);
        $licence->shouldReceive('getLatestBusVariation')->once()->with($existingRegNo)->andReturnNull();
        $licence->expects('getLatestBusVariation')->with($existingRegNo, [])->andReturnNull();

        $this->repoMap['Licence']->shouldReceive('fetchByLicNoWithoutAdditionalData')
            ->once()
            ->with($parsedLicenceNumber)
            ->andReturn($licence);

        $extraProcessedEbsrData = [
            'subsidised' => $this->refData['bs_in_part'],
            'trafficAreas' => $this->trafficAreas($parsedTrafficAreas, $trafficArea1, $trafficArea2, $trafficArea3),
            'naptanAuthorities' => $this->naptan($naptanCodes),
            'busNoticePeriod' => $this->references[BusNoticePeriodEntity::class][1],
            'localAuthoritys' => $this->localAuthorities($parsedLocalAuthorities, $trafficArea2, $trafficArea3),
            'busServiceTypes' => $this->busServiceTypes($busServiceTypes),
            'serviceNo' => 'BUS005',
            'otherServiceNumbers' => ['123', '456']
        ];

        $processedDataOutput = array_merge($parsedEbsrData, $extraProcessedEbsrData);
        unset($processedDataOutput['lineNames']); // Remove lineNames as it gets transformed
        $processedContext = [
            'busReg' => null,
            'busRegNoExclusions' => null,
        ];

        //this is just validators for the data generated by doctrine, no data is filtered
        $this->mockInput(ProcessedDataInputFactory::class, $processedDataOutput, $processedContext, $processedDataOutput);

        //this is just validators for short notice information, no data is filtered
        $this->mockInputFailure(ShortNoticeInputFactory::class, $processedDataOutput, m::type('array'), ['messages']);

        $this->expectedEmailQueueSideEffect(SendEbsrErrorsCmd::class, ['id' => $ebsrSubId], $ebsrSubId, new Result());
        $this->sut->handleCommand($command);
    }

    /**
     * Common assertions for ebsr submission entity
     *
     * @param $ebsrSubId
     * @param $organisation
     * @param $document
     *
     * @return m\MockInterface
     */
    private function failedEbsrSubmission($ebsrSubId, $organisation, $document): m\MockInterface
    {
        $ebsrSubmission = m::mock(EbsrSubmissionEntity::class);

        $ebsrSubmission->shouldReceive('getId')->andReturn($ebsrSubId);
        $ebsrSubmission->shouldReceive('getOrganisation')->once()->andReturn($organisation);
        $ebsrSubmission->shouldReceive('getDocument')->once()->andReturn($document);

        $ebsrSubmission->shouldReceive('beginValidating')
            ->once()
            ->with($this->refData[EbsrSubmissionEntity::VALIDATING_STATUS])
            ->andReturnSelf();

        $ebsrSubmission->shouldReceive('finishValidating')
            ->once()
            ->with($this->refData[EbsrSubmissionEntity::FAILED_STATUS], ['an_array'])
            ->andReturnSelf();

        return $ebsrSubmission;
    }

    /**
     * Common assertions for ebsr submission repo
     *
     * @param $command
     * @param $ebsrSubmission
     * @param $timesSaved
     */
    private function ebsrSubmissionRepo($command, $ebsrSubmission, $timesSaved): void
    {
        $this->repoMap['EbsrSubmission']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($ebsrSubmission);

        $this->repoMap['EbsrSubmission']->shouldReceive('save')
            ->times($timesSaved)
            ->with(m::type(EbsrSubmissionEntity::class));
    }

    /**
     * Common assertions for document entity
     *
     * @param $docIdentifier
     * @param $documentDescription
     *
     * @return m\MockInterface
     */
    private function basicDocument($docIdentifier, $documentDescription): m\MockInterface
    {
        $document = m::mock(DocumentEntity::class);
        $document->shouldReceive('getIdentifier')->once()->andReturn($docIdentifier);
        $document->shouldReceive('getDescription')->once()->andReturn($documentDescription);

        return $document;
    }

    /**
     * Common assertions for file processor
     *
     * @param $docIdentifier
     * @param $xmlName
     */
    private function fileProcessor($docIdentifier, $xmlName): void
    {
        $this->mockedSmServices[FileProcessorInterface::class]
            ->shouldReceive('setSubDirPath')
            ->with('root')
            ->once();
        $this->mockedSmServices[FileProcessorInterface::class]
            ->shouldReceive('fetchXmlFileNameFromDocumentStore')
            ->with($docIdentifier)
            ->once()
            ->andReturn($xmlName);
    }

    /**
     * Common assertions for traffic areas
     *
     * @param $parsedTrafficAreas
     * @param $trafficArea1
     * @param $trafficArea2
     * @param $trafficArea3
     *
     * @return ArrayCollection
     */
    private function trafficAreas($parsedTrafficAreas, $trafficArea1, $trafficArea2, $trafficArea3): ArrayCollection
    {
        $trafficAreas = [
            0 => $trafficArea1
        ];

        $this->repoMap['TrafficArea']->shouldReceive('fetchByTxcName')
            ->once()
            ->with($parsedTrafficAreas)
            ->andReturn($trafficAreas);

        //includes traffic areas from the local authorities
        return new ArrayCollection([$trafficArea1, $trafficArea2, $trafficArea3]);
    }

    /**
     * Common assertions for local authorities
     *
     * @param $parsedLocalAuthorities
     * @param $firstTa
     * @param $secondTa
     *
     * @return ArrayCollection
     */
    private function localAuthorities($parsedLocalAuthorities, $firstTa, $secondTa): ArrayCollection
    {
        $localAuthority1 = m::mock(LocalAuthorityEntity::class);
        $localAuthority1->shouldReceive('getTrafficArea')->andReturn($firstTa);
        $localAuthority2 = m::mock(LocalAuthorityEntity::class);
        $localAuthority2->shouldReceive('getTrafficArea')->andReturn($secondTa);

        $localAuthorities = [
            0 => $localAuthority1,
            1 => $localAuthority2,
        ];

        $this->repoMap['LocalAuthority']->shouldReceive('fetchByTxcName')
            ->once()
            ->with($parsedLocalAuthorities)
            ->andReturn($localAuthorities);

        return new ArrayCollection([$localAuthority1, $localAuthority2]);
    }

    /**
     * Common assertions for bus service types
     *
     * @param array $busServiceTypes
     *
     * @return ArrayCollection
     */
    private function busServiceTypes($busServiceTypes): ArrayCollection
    {
        $busServiceTypeKeys = array_keys($busServiceTypes);

        $busServiceType1 = m::mock(BusServiceTypeEntity::class);
        $busServiceType2 = m::mock(BusServiceTypeEntity::class);

        $busServiceTypeResult = [
            0 => $busServiceType1,
            1 => $busServiceType2
        ];

        $this->repoMap['BusServiceType']->shouldReceive('fetchByTxcName')
            ->once()
            ->with($busServiceTypeKeys)
            ->andReturn($busServiceTypeResult);

        return new ArrayCollection([$busServiceType1, $busServiceType2]);
    }

    /**
     * Common assertions for naptan codes
     *
     * @param $naptanCodes
     *
     * @return ArrayCollection
     */
    private function naptan($naptanCodes): ArrayCollection
    {
        $naptanAuthority1 = m::mock(LocalAuthorityEntity::class);
        $naptanAuthority2 = m::mock(LocalAuthorityEntity::class);

        $naptanAuthorities = [
            0 => $naptanAuthority1,
            1 => $naptanAuthority2,
        ];

        $this->repoMap['LocalAuthority']->shouldReceive('fetchByNaptan')
            ->once()
            ->with($naptanCodes)
            ->andReturn($naptanAuthorities);

        return new ArrayCollection([$naptanAuthority1, $naptanAuthority2]);
    }

    /**
     * Common assertions to mock input filter
     *
     * @param $inputName
     * @param $inputValue
     * @param $context
     * @param $outputValue
     */
    private function mockInput($inputName, $inputValue, $context, $outputValue): void
    {
        $this->mockedSmServices[$inputName]
            ->shouldReceive('setValue')
            ->once()
            ->with($inputValue)
            ->andReturnSelf();
        $this->mockedSmServices[$inputName]
            ->shouldReceive('isValid')
            ->with($context)
            ->once()
            ->andReturn(true);
        $this->mockedSmServices[$inputName]
            ->shouldReceive('getValue')
            ->once()
            ->andReturn($outputValue);
    }

    /**
     * Common assertions to mock failed input filter
     *
     * @param $inputName
     * @param $inputValue
     * @param $context
     * @param $messages
     */
    private function mockInputFailure($inputName, $inputValue, $context, $messages): void
    {
        $this->mockedSmServices[$inputName]
            ->shouldReceive('setValue')
            ->once()
            ->with($inputValue)
            ->andReturnSelf();
        $this->mockedSmServices[$inputName]
            ->shouldReceive('isValid')
            ->with($context)
            ->once()
            ->andReturn(false);
        $this->mockedSmServices[$inputName]
            ->shouldReceive('getValue')
            ->once()
            ->andReturn($inputValue);
        $this->mockedSmServices[$inputName]
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn($messages);
    }

    /**
     * Common assertions to mock side effects when submission is successful
     *
     * @param $savedBusRegId
     * @param $licenceId
     * @param $documentId
     */
    private function successSideEffects($savedBusRegId, $licenceId, $documentId, $fee): void
    {
        $this->documentLinkSideEffect($documentId, $savedBusRegId, $licenceId);
        $this->requestMapSideEffect($savedBusRegId);
        $this->txcInboxSideEffect($savedBusRegId);

        if ($fee) {
            $this->busFeeSideEffect($savedBusRegId);
        }
    }

    /**
     * Common assertions for supporting document side effect
     *
     * @param $docPath
     * @param $busRegId
     * @param $licenceId
     * @param $filename
     * @param $description
     */
    private function supportingDocSideEffect($docPath, $busRegId, $licenceId, $filename, $description): void
    {
        $documentData = [
            'content' => base64_encode(file_get_contents($docPath)),
            'busReg' => $busRegId,
            'licence' => $licenceId,
            'category' => CategoryEntity::CATEGORY_BUS_REGISTRATION,
            'subCategory' => CategoryEntity::BUS_SUB_CATEGORY_OTHER_DOCUMENTS,
            'filename' => $filename,
            'description' => $description
        ];

        $this->expectedSideEffect(UploadCmd::class, $documentData, new Result());
    }

    /**
     * Common assertions for side effect updating document links
     *
     * @param $documentId
     * @param $savedBusRegId
     * @param $licenceId
     */
    private function documentLinkSideEffect($documentId, $savedBusRegId, $licenceId): void
    {
        $documentLinkData = [
            'id' => $documentId,
            'busReg' => $savedBusRegId,
            'licence' => $licenceId,
        ];

        $this->expectedSideEffect(UpdateDocumentLinksCmd::class, $documentLinkData, new Result());
    }

    /**
     * Common assertions for a request map side effect
     *
     * @param $savedBusRegId
     */
    private function requestMapSideEffect($savedBusRegId): void
    {
        $requestMapData = [
            'id' => $savedBusRegId,
            'scale' => 'auto'
        ];

        $this->expectedSideEffect(RequestMapQueueCmd::class, $requestMapData, new Result());
    }

    /**
     * Common assertions for bus fee side effect
     *
     * @param $savedBusRegId
     */
    private function busFeeSideEffect($savedBusRegId): void
    {
        $this->expectedSideEffect(CreateBusFeeCmd::class, ['id' => $savedBusRegId], new Result());
    }

    /**
     * Common assertions for txc inbox side effect
     *
     * @param $savedBusRegId
     */
    private function txcInboxSideEffect($savedBusRegId): void
    {
        $this->expectedSideEffect(CreateTxcInboxCmd::class, ['id' => $savedBusRegId], new Result());
    }
}

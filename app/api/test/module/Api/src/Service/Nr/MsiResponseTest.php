<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nr;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as ErruRequestEntity;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty as SiPenaltyEntity;
use Dvsa\Olcs\Api\Service\Nr\MsiResponse;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\XmlTools\Xml\XmlNodeBuilder;

class MsiResponseTest extends MockeryTestCase
{
    public function testCreateThrowsException(): void
    {
        $this->expectException(ForbiddenException::class);

        $cases = m::mock(CasesEntity::class);
        $cases->expects('canSendMsiResponse')->withNoArgs()->andReturnFalse();

        $sut = new MsiResponse(m::mock(XmlNodeBuilder::class), "3.4");
        $sut->create($cases);
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(?string $licence, string $authority, string $memberStateCode, string $filteredMemberStateCode): void
    {
        $siPenaltyTypeId1 = 101;
        $siPenaltyTypeId2 = 102;
        $reasonNotImposed = 'reason not imposed';
        $notificationNumber = 214124;
        $erruOriginatingAuthority = 'originating authority';
        $erruTransportUndertaking = 'transport undertaking';
        $startDate = '2015-01-31';
        $endDate = '2015-05-16';
        $workflowId = "FB4F5CE2-4D38-4AB8-8185-03947C939393";
        $communityLicenceNumber = 'GBUK/OB1234567/00001';
        $totAuthVehicles = 10;
        $communityLicenceStatus = 'Active';
        $schemaVersion = '3.4';

        $penalty1 = m::mock(SiPenaltyEntity::class)->makePartial();
        $penalty1->expects('getSiPenaltyType->getId')->withNoArgs()->andReturn($siPenaltyTypeId1);
        $penalty1->expects('getStartDate')->withNoArgs()->andReturnNull();
        $penalty1->expects('getEndDate')->withNoArgs()->andReturnNull();
        $penalty1->expects('getImposed')->withNoArgs()->andReturn('N');
        $penalty1->expects('getReasonNotImposed')->withNoArgs()->andReturn($reasonNotImposed);

        $penalty2 = m::mock(SiPenaltyEntity::class);
        $penalty2->shouldReceive('getSiPenaltyType->getId')->once()->andReturn($siPenaltyTypeId2);
        $penalty2->expects('getStartDate')->withNoArgs()->andReturn($startDate);
        $penalty2->expects('getEndDate')->withNoArgs()->andReturn($endDate);
        $penalty2->expects('getImposed')->withNoArgs()->andReturn('Y');
        $penalty2->shouldReceive('getReasonNotImposed')->never();

        $appliedPenalties = new PersistentCollection(
            m::mock(EntityManagerInterface::class),
            SiPenaltyEntity::class,
            new ArrayCollection([$penalty1, $penalty2])
        );

        $seriousInfringement = m::mock(SiEntity::class);
        $seriousInfringement->expects('getAppliedPenalties')->withNoArgs()->andReturn($appliedPenalties);

        $seriousInfringements = new ArrayCollection([$seriousInfringement]);

        $erruRequest = m::mock(ErruRequestEntity::class);
        $erruRequest->expects('getNotificationNumber')->withNoArgs()->andReturn($notificationNumber);
        $erruRequest->expects('getWorkflowId')->withNoArgs()->andReturn($workflowId);
        $erruRequest->expects('getMemberStateCode->getId')->withNoArgs()->andReturn($memberStateCode);
        $erruRequest->expects('getTransportUndertakingName')->withNoArgs()->andReturn($erruTransportUndertaking);
        $erruRequest->expects('getOriginatingAuthority')->withNoArgs()->andReturn($erruOriginatingAuthority);
        $erruRequest->expects('getCommunityLicenceNumber')->withNoArgs()->andReturn($communityLicenceNumber);
        $erruRequest->expects('getTotAuthVehicles')->withNoArgs()->andReturn($totAuthVehicles);
        $erruRequest->expects('getCommunityLicenceStatus->getDescription')->withNoArgs()->andReturn($communityLicenceStatus);

        $cases = m::mock(CasesEntity::class);
        $cases->expects('canSendMsiResponse')->withNoArgs()->andReturnTrue();
        $cases->expects('getSeriousInfringements')->withNoArgs()->andReturn($seriousInfringements);
        $cases->expects('getLicence')->withNoArgs()->andReturn($licence);
        $cases->expects('getErruRequest')->withNoArgs()->andReturn($erruRequest);

        $expectedXmlResponse = 'xml';
        $xmlNodeBuilder = m::mock(XmlNodeBuilder::class)->makePartial();
        $xmlNodeBuilder->expects('buildTemplate')->withNoArgs()->andReturn($expectedXmlResponse);

        $sut = new MsiResponse($xmlNodeBuilder, $schemaVersion);
        $actualXmlResponse = $sut->create($cases);

        $header = [
            'name' => 'Header',
            'attributes' => [
                'version' => $schemaVersion,
                'technicalId' => $sut->getTechnicalId(),
                'workflowId' => $workflowId,
                'sentAt' => $sut->getResponseDateTime(),
                'timeoutValue' => $sut->getTimeoutDateTime(),
                'from' => 'UK',
                'to' => $filteredMemberStateCode
            ],
        ];

        $body = [
            'name' => 'Body',
            'attributes' => [
                'businessCaseId' => $notificationNumber,
                'originatingAuthority' => $erruOriginatingAuthority,
                'respondingAuthority' => $authority,
                'statusCode' => 'OK',
            ],
            'nodes' => [
                0 => [
                    'name' => 'TransportUndertaking',
                    'attributes' => [
                        'transportUndertakingName' => $erruTransportUndertaking,
                        'communityLicenceNumber' => $communityLicenceNumber,
                        'communityLicenceStatus' => $communityLicenceStatus,
                        'numberOfVehicles' => $totAuthVehicles,
                    ],
                    'nodes' => [
                        0 => [
                            'name' => 'TransportUndertakingAddress',
                            'attributes' => [
                                'address' => 'address',
                                'postcode' => 'postcode',
                                'city' => 'city',
                                'country' => 'UK',
                            ],
                        ],
                    ],
                ],
                1 => [
                    'name' => 'PenaltiesImposed',
                    'nodes' => [
                        0 => [
                            'name' => 'PenaltyImposed',
                            'attributes' => [
                                'authorityImposingPenalty' => $authority,
                                'penaltyTypeImposed' => $siPenaltyTypeId1,
                                'isImposed' => 'false',
                                'reasonNotImposed' => $reasonNotImposed,
                            ]
                        ],
                        1 => [
                            'name' => 'PenaltyImposed',
                            'attributes' => [
                                'authorityImposingPenalty' => $authority,
                                'penaltyTypeImposed' => $siPenaltyTypeId2,
                                'isImposed' => 'true',
                                'startDate' => $startDate,
                                'endDate' => $endDate,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expectedXmlData = [
            'Header' => $header,
            'Body' => $body
        ];

        $this->assertEquals($expectedXmlData, $sut->getXmlBuilder()->getData());
        $this->assertEquals($expectedXmlResponse, $actualXmlResponse);
    }

    /**
     * Data provider for testCreate
     *
     * @return array
     */
    public function createDataProvider(): array
    {
        return [
            [null, MsiResponse::AUTHORITY_TRU, 'GB', 'UK'],
            ['licence', MsiResponse::AUTHORITY_TC, 'GB', 'UK'],
            [null, MsiResponse::AUTHORITY_TRU, 'PL', 'PL'],
            ['licence', MsiResponse::AUTHORITY_TC, 'PL', 'PL'],
            [null, MsiResponse::AUTHORITY_TRU, 'ES', 'ES'],
            ['licence', MsiResponse::AUTHORITY_TC, 'ES', 'ES']
        ];
    }
}

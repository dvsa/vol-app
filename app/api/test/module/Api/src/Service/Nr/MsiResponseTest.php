<?php

namespace Dvsa\OlcsTest\Api\Service\Nr;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as ErruRequestEntity;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty as SiPenaltyEntity;
use Dvsa\Olcs\Api\Service\Nr\MsiResponse;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\XmlTools\Xml\XmlNodeBuilder;

/**
 * Class MsiResponseTest
 * @package Dvsa\OlcsTest\Api\Service\Nr
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class MsiResponseTest extends MockeryTestCase
{
    public function testCreateThrowsException()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ForbiddenException::class);

        $cases = m::mock(CasesEntity::class);
        $cases->shouldReceive('canSendMsiResponse')->once()->andReturn(false);

        $sut = new MsiResponse(m::mock(XmlNodeBuilder::class));
        $sut->create($cases);
    }

    /**
     * Tests create
     *
     * @param $licence
     * @param $authority
     * @param $memberStateCode
     * @param $filteredMemberStateCode
     *
     * @dataProvider createDataProvider
     */
    public function testCreate($licence, $authority, $memberStateCode, $filteredMemberStateCode)
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

        $penalty1 = m::mock(SiPenaltyEntity::class)->makePartial();
        $penalty1->shouldReceive('getSiPenaltyType->getId')->once()->andReturn($siPenaltyTypeId1);
        $penalty1->shouldReceive('getStartDate')->once()->andReturn(null);
        $penalty1->shouldReceive('getEndDate')->once()->andReturn(null);
        $penalty1->shouldReceive('getImposed')->once()->andReturn('N');
        $penalty1->shouldReceive('getReasonNotImposed')->once()->andReturn($reasonNotImposed);

        $penalty2 = m::mock(SiPenaltyEntity::class);
        $penalty2->shouldReceive('getSiPenaltyType->getId')->once()->andReturn($siPenaltyTypeId2);
        $penalty2->shouldReceive('getStartDate')->once()->andReturn($startDate);
        $penalty2->shouldReceive('getEndDate')->once()->andReturn($endDate);
        $penalty2->shouldReceive('getImposed')->once()->andReturn('Y');
        $penalty2->shouldReceive('getReasonNotImposed')->never();

        $appliedPenalties = new PersistentCollection(
            m::mock(EntityManagerInterface::class),
            SiPenaltyEntity::class,
            new ArrayCollection([$penalty1, $penalty2])
        );

        $seriousInfringement = m::mock(SiEntity::class);
        $seriousInfringement->shouldReceive('getAppliedPenalties')->once()->andReturn($appliedPenalties);

        $seriousInfringements = new ArrayCollection([$seriousInfringement]);

        $erruRequest = m::mock(ErruRequestEntity::class);
        $erruRequest->shouldReceive('getNotificationNumber')->once()->andReturn($notificationNumber);
        $erruRequest->shouldReceive('getWorkflowId')->once()->andReturn($workflowId);
        $erruRequest->shouldReceive('getMemberStateCode->getId')->once()->andReturn($memberStateCode);
        $erruRequest->shouldReceive('getTransportUndertakingName')->once()->andReturn($erruTransportUndertaking);
        $erruRequest->shouldReceive('getOriginatingAuthority')->once()->andReturn($erruOriginatingAuthority);

        $cases = m::mock(CasesEntity::class);
        $cases->shouldReceive('canSendMsiResponse')->once()->andReturn(true);
        $cases->shouldReceive('getSeriousInfringements')->once()->andReturn($seriousInfringements);
        $cases->shouldReceive('getLicence')->once()->andReturn($licence);
        $cases->shouldReceive('getErruRequest')->once()->andReturn($erruRequest);

        $expectedXmlResponse = 'xml';
        $xmlNodeBuilder = m::mock(XmlNodeBuilder::class)->makePartial();
        $xmlNodeBuilder->shouldReceive('buildTemplate')->once()->andReturn($expectedXmlResponse);

        $sut = new MsiResponse($xmlNodeBuilder);
        $actualXmlResponse = $sut->create($cases);

        $header = [
            'name' => 'Header',
            'attributes' => [
                'technicalId' => $sut->getTechnicalId(),
                'workflowId' => $workflowId,
                'sentAt' => $sut->getResponseDateTime(),
                'from' => 'UK'
            ],
            'nodes' => [
                0 => [
                    'name' => 'To',
                    'nodes' => [
                        0 => [
                            'name' => 'MemberState',
                            'attributes' => [
                                'code' => $filteredMemberStateCode
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $body = [
            'name' => 'Body',
            'attributes' => [
                'businessCaseId' => $notificationNumber,
                'originatingAuthority' => $erruOriginatingAuthority,
                'licensingAuthority' => $authority,
                'responseDateTime' => $sut->getResponseDateTime()
            ],
            'nodes' => [
                0 => [
                    'name' => 'TransportUndertaking',
                    'attributes' => [
                        'name' => $erruTransportUndertaking
                    ],
                    'nodes' => [
                        0 => [
                            'name' => 'PenaltyImposed',
                            'attributes' => [
                                'authorityImposingPenalty' => $authority,
                                'penaltyTypeImposed' => $siPenaltyTypeId1,
                                'isImposed' => 'No',
                                'reasonNotImposed' => $reasonNotImposed
                            ]
                        ],
                        1 => [
                            'name' => 'PenaltyImposed',
                            'attributes' => [
                                'authorityImposingPenalty' => $authority,
                                'penaltyTypeImposed' => $siPenaltyTypeId2,
                                'isImposed' => 'Yes',
                                'startDate' => $startDate,
                                'endDate' => $endDate
                            ]
                        ]
                    ]
                ]
            ]
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
    public function createDataProvider()
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

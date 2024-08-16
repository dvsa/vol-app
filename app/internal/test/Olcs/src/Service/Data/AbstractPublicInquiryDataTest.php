<?php

namespace OlcsTest\Service\Data;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Data\AbstractPublicInquiryData;

/**
 * @covers Olcs\Service\Data\AbstractPublicInquiryData
 */
class AbstractPublicInquiryDataTest extends MockeryTestCase
{
    public const LIC_ID = 9999;
    public const APP_ID = 8888;

    /** @var  m\MockInterface | AbstractPublicInquiryData */
    private $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(AbstractPublicInquiryData::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testFetchListOptionsLicGoodsOrPsvIsNullAndAppIdIsNull()
    {
        $this->sut->shouldReceive('getLicenceContext')
            ->once()
            ->andReturn(
                [
                    'unit_LicCtxKey' => 'unit_LicCtxVal',
                ]
            );

        $licData = [
            'applications' => [
                0 => [
                    'id' => self::APP_ID,
                ],
            ],
        ];

        $this->sut->shouldReceive('getLicenceService->getId')->once()->andReturn(self::LIC_ID);
        $this->sut->shouldReceive('getLicenceService->fetchLicenceData')
            ->once()
            ->with(self::LIC_ID)
            ->andReturn($licData);
        $this->sut->shouldReceive('getApplicationService->getId')->once()->andReturnNull();
        $this->sut->shouldReceive('getApplicationService->setId')->once()->with(self::APP_ID);
        $this->sut->shouldReceive('getApplicationContext')
            ->once()
            ->andReturn(
                [
                    'goodsOrPsv' => 'unit_AppGoodsAndPsv',
                ]
            );
        $this->sut->shouldReceive('fetchPublicInquiryData')
            ->once()
            ->with(
                [
                    'unit_LicCtxKey' => 'unit_LicCtxVal',
                    'goodsOrPsv' => 'unit_AppGoodsAndPsv',
                ]
            )
            ->andReturn('DATA');

        //  call & check
        $actual = $this->sut->fetchListOptions(null);

        static::assertEquals([], $actual);
    }

    public function testFetchListOptionsEmptyLicenceId()
    {
        $expectPubInqData = ['DATA'];
        $ctx = [
            'unit_CtxKey' => 'unit_CtxValue',
        ];

        $this->sut->shouldReceive('getLicenceContext')
            ->once()
            ->andReturn(
                [
                    'unit_LicCtxKey' => 'unit_LicCtxVal',
                ]
            );
        $this->sut->shouldReceive('getLicenceService->getId')->once()->andReturn(null);
        $this->sut->shouldReceive('fetchPublicInquiryData')
            ->once()
            ->with(
                [
                    'unit_CtxKey' => 'unit_CtxValue',
                    'unit_LicCtxKey' => 'unit_LicCtxVal',
                    'goodsOrPsv' => 'NULL',
                ]
            )
            ->andReturn($expectPubInqData);
        $this->sut->shouldReceive('formatData')->once()->with($expectPubInqData)->andReturn('EXPECT');

        //  call & check
        $actual = $this->sut->fetchListOptions($ctx);

        static::assertEquals('EXPECT', $actual);
    }

    public function testFetchListOptionsEmptyLicenceIdUseGroup()
    {
        $expectPubInqData = ['DATA'];

        $this->sut->shouldReceive('getLicenceContext')
            ->once()
            ->andReturn(
                [
                    'unit_LicCtxKey' => 'unit_LicCtxVal',
                ]
            );
        $this->sut->shouldReceive('getLicenceService->getId')->once()->andReturn(null);
        $this->sut->shouldReceive('fetchPublicInquiryData')
            ->once()
            ->with(
                [
                    'unit_LicCtxKey' => 'unit_LicCtxVal',
                    'goodsOrPsv' => 'NULL',
                ]
            )
            ->andReturn($expectPubInqData);
        $this->sut->shouldReceive('formatDataForGroups')->once()->with($expectPubInqData)->andReturn('EXPECT');

        //  call & check
        $actual = $this->sut->fetchListOptions(null, true);

        static::assertEquals('EXPECT', $actual);
    }
}

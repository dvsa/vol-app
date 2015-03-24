<?php

/**
 * Licence Controller Trait Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Traits;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\LicenceEntityService;

/**
 * Licence Controller Trait Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceControllerTraitTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock('\OlcsTest\Controller\Lva\Traits\Stubs\LicenceControllerTraitStub')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testGetHeaderParams()
    {
        $licenceId = 69;

        $this->sut->shouldReceive('getLicenceId')->andReturn($licenceId);

        $licenceData = [
            'id' => 69,
            'goodsOrPsv' => ['id' => LicenceEntityService::LICENCE_CATEGORY_PSV],
            'licNo' => 'OB123',
            'organisation' => ['id' => 99, 'name' => 'Stolenegg Ltd.'],
            'status' => [
                'id' => LicenceEntityService::LICENCE_STATUS_VALID,
                'description' => 'Valid'
            ],
        ];

        $this->sm->setService(
            'Entity\Licence',
            m::mock()
                ->shouldReceive('getHeaderParams')
                ->once()
                ->with($licenceId)
                ->andReturn($licenceData)
                ->getMock()
        );

        $expectedData = [
            'licNo' => 'OB123',
            'companyName' => 'Stolenegg Ltd.',
            'description' => 'Valid',
            'statusColour' => 'green',
        ];

        $this->assertEquals($expectedData, $this->sut->getHeaderParams());
    }

    /**
     * @dataProvider colourStatusProvider
     * @param string $status
     * @param string $expectedColour
     */
    public function testGetColourForStatus($status, $expectedColour)
    {
        $this->assertEquals($expectedColour, $this->sut->getColourForStatus($status));
    }

    public function colourStatusProvider()
    {
        return [
            [LicenceEntityService::LICENCE_STATUS_UNDER_CONSIDERATION, 'orange'],
            [LicenceEntityService::LICENCE_STATUS_NOT_SUBMITTED, 'grey'],
            [LicenceEntityService::LICENCE_STATUS_SUSPENDED, 'orange'],
            [LicenceEntityService::LICENCE_STATUS_VALID, 'green'],
            [LicenceEntityService::LICENCE_STATUS_CURTAILED, 'orange'],
            [LicenceEntityService::LICENCE_STATUS_GRANTED, 'orange'],
            [LicenceEntityService::LICENCE_STATUS_SURRENDERED, 'red'],
            [LicenceEntityService::LICENCE_STATUS_WITHDRAWN, 'red'],
            [LicenceEntityService::LICENCE_STATUS_REFUSED, 'red'],
            [LicenceEntityService::LICENCE_STATUS_REVOKED, 'red'],
            [LicenceEntityService::LICENCE_STATUS_NOT_TAKEN_UP, 'red'],
            [LicenceEntityService::LICENCE_STATUS_TERMINATED, 'red'],
            [LicenceEntityService::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT, 'red'],
            ['somethingelse', 'grey'],
        ];
    }
}

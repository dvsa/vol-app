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
        $this->markTestSkipped();
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

        $this->sut->shouldReceive('handleQuery')
            ->with(
                m::on(
                    function ($query) use ($licenceId) {
                        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\Licence\Licence::class, $query);
                        $this->assertEquals($licenceId, $query->getId());
                        return true;
                    }
                )
            )
            ->andReturn(
                m::mock(\Common\Service\Cqrs::class)
                    ->shouldReceive('getResult')
                    ->andReturn($licenceData)
                    ->getMock()
            );

        $expectedData = [
            'licNo' => 'OB123',
            'companyName' => 'Stolenegg Ltd.',
            'status' => 'lsts_valid',
        ];

        $this->assertEquals($expectedData, $this->sut->getHeaderParams());
    }
}

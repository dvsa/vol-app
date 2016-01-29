<?php

/**
 * External Licence Vehicles Goods Controller Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Licence;

use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;

/**
 * External Licence Vehicles Goods Controller Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesControllerTest extends MockeryTestCase
{
    use ControllerTestTrait;

    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    public function setUp()
    {
        $this->markTestSkipped();
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Licence\VehiclesController');
    }

    public function testAlterTableAddsExportAction()
    {
        $table = m::mock()
            ->shouldReceive('addAction')
            ->with(
                'export',
                [
                    'requireRows' => true,
                    'class' => 'secondary js-disable-crud'
                ]
            )
            // this happens in the abstract; that's in common
            // and not something we care about at this level of test
            // hence we don't pass a with() condition
            ->shouldReceive('removeAction')
            ->getMock();

        $this->sut->alterTable($table);
    }

    public function testAlternativeCrudActionWithExport()
    {
        $response = m::mock();
        $table = m::mock();

        $this->sut->shouldReceive('getResponse')
            ->andReturn($response)
            ->shouldReceive('getTable')
            ->andReturn($table);

        $this->request
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->with('query')
            ->andReturn(
                [
                    'limit' => '5',
                    'vrm' => 'A'
                ]
            )
            ->shouldReceive('getPost')
            ->andReturn(
                m::mock()
                ->shouldReceive('set')
                ->with(
                    'query',
                    [
                        'page' => 1,
                        'limit' => 'all',
                        'vrm' => 'A'
                    ]
                )
                ->getMock()
            );

        $this->setService(
            'Helper\Response',
            m::mock()
            ->shouldReceive('tableToCsv')
            ->with($response, $table, 'vehicles')
            ->getMock()
        );

        $this->sut->checkForAlternativeCrudAction('export');
    }
}

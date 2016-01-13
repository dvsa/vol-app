<?php

/**
 * External Licence Vehicles PSV Controller Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Licence;

use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;

/**
 * External Licence Vehicles PSV Controller Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvControllerTest extends MockeryTestCase
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

        $this->mockController('\Olcs\Controller\Lva\Licence\VehiclesPsvController');
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
            ->getMock();

        $this->sut->alterTable($table);
    }

    public function testAlternativeCrudActionWithExport()
    {
        $response = m::mock();
        $table = m::mock();

        $this->sut->shouldReceive('getResponse')
            ->andReturn($response)
            ->shouldReceive('getType')
            ->andReturn('small')
            ->shouldReceive('getTable')
            ->with('small')
            ->andReturn($table);

        $this->setService(
            'Helper\Response',
            m::mock()
            ->shouldReceive('tableToCsv')
            ->with($response, $table, 'small-vehicles')
            ->getMock()
        );

        $this->sut->checkForAlternativeCrudAction('export');
    }
}

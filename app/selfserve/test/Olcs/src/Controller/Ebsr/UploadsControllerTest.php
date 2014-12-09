<?php

namespace OlcsTest\Controller\Ebsr;

use Olcs\Controller\Ebsr\UploadsController;
use Mockery as m;

/**
 * Class UploadsControllerTest
 * @package OlcsTest\Controller\Ebsr
 */
class UploadsControllerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testIndexAction()
    {
        $fakeData = [
            [
                'status' => 'Recieved',
                'filename' => 'PB000679.zip',
                'submitted' => '2014-10-07'
            ]
        ];

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
                  ->with('ebsr-packs', $fakeData, m::type('array'), false)
                  ->andReturn('table');

        $mockDataService = m::mock('Olcs\Service\Data\EbsrPack');
        $mockDataService->shouldReceive('fetchList')->with()->andReturn($fakeData);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\EbsrPack')->andReturn($mockDataService);

        $sut = new UploadsController();
        $sut->setServiceLocator($mockSl);

        $result = $sut->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertEquals('table', $result->table);
    }
}

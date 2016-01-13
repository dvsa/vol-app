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

    public function testProcessSave()
    {
        $sut = new UploadsController();

        $mockResult = [
            'success' => 'success message',
            'errors' => [
                'message1',
                'message2'
            ]
        ];
        $mockEbsrService = m::mock('Olcs\Service\Ebsr');
        $mockEbsrService->shouldReceive('processPackUpload')
            ->with(m::type('array'), m::type('string'))
            ->andReturn($mockResult);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\Service\Ebsr')->andReturn($mockEbsrService);
        $sut->setServiceLocator($mockSl);

        $mockData = [
            'fields' => [
                'submissionType' => 'subType',
                'file' => 'somefile'
            ]
        ];
        $form = m::mock('\Zend\Form\Form');
        $form->shouldReceive('setMessages')->with(['fields' => ['file' => $mockResult['errors']]]);

        $sut->processSave($mockData, $form);
    }
}

<?php

/**
 * Impounding Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery as m;

/**
 * Impounding Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ImpoundingControllerTest extends AbstractHttpControllerTestCase
{
    protected $testClass = 'Olcs\Controller\Cases\Impounding\ImpoundingController';

    public function testProcessSave()
    {
        $inData = [
            'fields' => [
                'piVenue' => 1,
                'piVenueOther' => 'this data will be made null'
            ]
        ];

        $outData = [
            'fields' => [
                'piVenue' => 1,
                'piVenueOther' => null
            ]
        ];

        $controller = $this->getMock(
            'Olcs\Controller\Cases\Impounding\ImpoundingController',
            ['addSuccessMessage', 'redirectToIndex', 'processDataMapForSave', 'save']
        );

        $controller->expects($this->once())->method('processDataMapForSave')
            ->with($outData)->will($this->returnValue($outData));

        $controller->expects($this->once())->method('save')
            ->with($outData)->will($this->returnValue(null));

        $controller->expects($this->once())->method('addSuccessMessage');

        $this->assertNull(null, $controller->processSave($inData));
    }

    public function testGenerateFormWithData()
    {
        $formName = 'impounding';
        $callback = 'processSave';
        $data = array();
        $tables = false;

        $piVenueMock = m::mock('\Zend\Form\Element');
        $piVenueOtherMock = m::mock('\Zend\Form\Element');
        $piVenueMock->shouldReceive('getValue')->andReturn(null);
        $piVenueMock->shouldReceive('setValue')->with('other');
        $piVenueOtherMock->shouldReceive('getValue')->andReturn('test data');

        $mockFormFields = m::mock('\Zend\Form\Fieldset');
        $mockFormFields->shouldReceive('get')->with('piVenue')->andReturn($piVenueMock);
        $mockFormFields->shouldReceive('get')->with('piVenueOther')->andReturn($piVenueOtherMock);

        $mockForm = m::mock('\Zend\Form\Form');
        $mockForm->shouldReceive('get')->with('fields')->andReturn($mockFormFields);

        $controller = $this->getMock(
            'Olcs\Controller\Cases\Impounding\ImpoundingController',
            ['callParentGenerateFormWithData']
        );

        $controller->expects($this->once())
            ->method('callParentGenerateFormWithData')
            ->with(
                $this->equalTo($formName),
                $this->equalTo($callback),
                $this->equalTo($data),
                $this->equalTo($tables)
            )
            ->will($this->returnValue($mockForm));

        $controller->generateFormWithData($formName, $callback, $data, $tables);
    }
}

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
}

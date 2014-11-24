<?php

/**
 * Pi Hearing Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery as m;

/**
 * Pi Hearing Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class HearingControllerTest extends AbstractHttpControllerTestCase
{
    protected $testClass = 'Olcs\Controller\Cases\PublicInquiry\HearingController';

    public function testProcessSave()
    {
        $inData = [
            'fields' => [
                'piVenue' => 1,
                'piVenueOther' => 'this data will be made null',
                'isCancelled' => 'N',
                'cancelledReason' => 'this data will be made null',
                'cancelledDate' => 'this data will be made null',
                'isAdjourned' => 'N',
                'adjournedReason' => 'this data will be made null',
                'adjournedDate' => 'this data will be made null',
                'pi' => 1
            ]
        ];

        $outData = [
            'fields' => [
                'piVenue' => 1,
                'piVenueOther' => null,
                'isCancelled' => 'N',
                'cancelledReason' => null,
                'cancelledDate' => null,
                'isAdjourned' => 'N',
                'adjournedReason' => null,
                'adjournedDate' => null,
                'pi' => [
                    'id' => 1,
                    'piStatus' => 'pi_s_schedule'
                ]
            ]
        ];

        $controller = $this->getMock(
            'Olcs\Controller\Cases\PublicInquiry\HearingController',
            ['addSuccessMessage', 'redirectToIndex', 'processDataMapForSave', 'save']
        );

        $controller->expects($this->once())->method('processDataMapForSave')
            ->with($outData)->will($this->returnValue($outData));

        $controller->expects($this->once())->method('save')
            ->with($outData)->will($this->returnValue(null));

        $controller->expects($this->once())->method('addSuccessMessage');

        $this->assertNull(null, $controller->processSave($inData));
    }

    public function testGetDataForForm()
    {
        $pi = 1;
        $data = [
            'fields' => [
                'pi' => $pi
            ]
        ];

        $controller = $this->getMock(
            'Olcs\Controller\Cases\PublicInquiry\HearingController',
            ['getFromRoute', 'getFormData']
        );

        $controller->expects($this->once())
            ->method('getFromRoute')
            ->with('pi')
            ->will($this->returnValue($pi));

        $controller->expects($this->once())
            ->method('getFormData')
            ->will($this->returnValue([]));

        $this->assertEquals($data, $controller->getDataForForm());
    }

    /**
     * Tests redirectToIndex
     */
    public function testRedirectToIndex()
    {
        $controller = $this->getMock(
            'Olcs\Controller\Cases\PublicInquiry\HearingController',
            ['redirectToRoute']
        );

        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('case_pi'),
                $this->equalTo(['action'=>'details']),
                $this->equalTo(['code' => '303']),
                $this->equalTo(true)
            );

        $controller->redirectToIndex();
    }
}

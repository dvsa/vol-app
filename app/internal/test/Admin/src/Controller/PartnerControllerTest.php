<?php

/**
 * Partner Controller Test
 *
 * @author Valtech <uk@valtech.co.uk>
 */

namespace AdminTest\Controller;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Partner Controller Test
 *
 * @author Valtech <uk@valtech.co.uk>
 */
class PartnerControllerTest extends MockeryTestCase
{
    /**
     * Tests only the code at hand.
     */
    public function testIndexAction()
    {
        $controller = $this->getMock(
            '\Admin\Controller\PartnerController',
            [
                'getViewHelperManager', 'getServiceLocator',
                'get', 'getContainer', 'append', 'parentIndexAction'
            ]
        );

        $controller->expects($this->once())->method('getViewHelperManager')->will($this->returnSelf());
        $controller->expects($this->once())->method('get')->will($this->returnSelf());
        $controller->expects($this->once())->method('getContainer')->will($this->returnSelf());
        $controller->expects($this->once())->method('append')->with('Partners')->will($this->returnSelf());
        $controller->expects($this->once())->method('parentIndexAction')->will($this->returnSelf());

        $controller->indexAction();
    }

    public function testProcessLoadWithData()
    {
        $controller = new \Admin\Controller\PartnerController();

        $data = [
            'id' => 1,
            'description' => 'Business Partner',
            'contactType' => [
                'id' => 'contact_type_id',
            ],
            'address' => [
                'addressLine1' => 'addressL1',
                'addressLine2' => 'addressL3',
                'town' => 'town1',
            ]
        ];

        $return = array (
            'fields' =>
                array (
                    'id' => 1,
                    'description' => 'Business Partner',
                    'contactType' => 'contact_type_id',
                    'address' =>
                        array (
                            'addressLine1' => 'addressL1',
                            'addressLine2' => 'addressL3',
                            'town' => 'town1',
                        ),
                ),
            'address' =>
                array (
                    'addressLine1' => 'addressL1',
                    'addressLine2' => 'addressL3',
                    'town' => 'town1',
                ),
        );

        $this->assertEquals($return, $controller->processLoad($data));
    }

    public function testProcessLoadWithNoExistingData()
    {
        $controller = new \Admin\Controller\PartnerController();

        $data = [
            'id' => '',
            'description' => 'Business Partner',
            'contactType' => [
                'id' => 'contact_type_id',
            ],
            'address' => [
                'addressLine1' => 'addressL1',
                'addressLine2' => 'addressL3',
                'town' => 'town1',
            ]
        ];

        $return = array();

        $this->assertEquals($return, $controller->processLoad($data));
    }

    public function testProcessSaveInIsolation()
    {
        $controller = $this->getMock(
            '\Admin\Controller\PartnerController',
            ['save', 'setIsSaved', 'addSuccessMessage', 'redirectToIndex']
        );

        $data = array (
            'fields' => [
                'id' => 1,
                'description' => 'Business Partner',
                'contactType' => 'contact_type_id',
            ],
            'address' => [
                'addressLine1' => 'addressL1',
                'addressLine2' => 'addressL3',
                'town' => 'town1',
            ],
        );

        $saveData = [
            'id' => 1,
            'description' => 'Business Partner',
            'contactType' => 'contact_type_id',
            'address' => [
                'addressLine1' => 'addressL1',
                'addressLine2' => 'addressL3',
                'town' => 'town1',
            ]
        ];

        $controller->expects($this->once())->method('setIsSaved')->with(true)->will($this->returnSelf());
        $controller->expects($this->once())->method('save')->with($saveData)->will($this->returnSelf());
        $controller->expects($this->once())->method('addSuccessMessage')->with('Saved successfully');
        $controller->expects($this->once())->method('redirectToIndex')->willReturn(null);

        $this->assertNull($controller->processSave($data));
    }
}

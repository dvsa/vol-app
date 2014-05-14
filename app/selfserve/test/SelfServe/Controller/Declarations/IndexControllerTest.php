<?php

/**
 * Test Declarations Index Controller
 */

namespace SelfServe\test\Controller\Declarations;

use PHPUnit_Framework_TestCase;
use SelfServe\Controller\Declarations\IndexController;

/**
 * Test Declarations Index Controller
 */
class IndexControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Build a mock controller
     *
     * @param array $methods
     */
    protected function createMockController($methods = array())
    {
        $this->controller = $this->getMock(
            'SelfServe\Controller\Declarations\IndexController',
            $methods
        );
    }

    public function testConstructorSetsCurrentSection()
    {
        // we have to opt-out of our standard mock builder here
        // as we want to disable the constructor ahead of
        // setting our expectations
        $className = 'SelfServe\Controller\Declarations\IndexController';
        $stub = $this->getMockBuilder($className)
            ->setMethods(['setCurrentSection'])
            ->disableOriginalConstructor()
            ->getMock();

        $stub->expects($this->once())
            ->method('setCurrentSection')
            ->with('declarations');

        $stub->__construct();
    }

    public function testIndexActionWithInvalidApplication()
    {
        $this->createMockController(
            ['makeRestCall', 'notFoundAction', 'params', 'setEventManager']
        );

        $mockParams = $this->getMock('\stdClass', ['fromRoute']);
        $mockParams->expects($this->once())
            ->method('fromRoute');

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(null));

        $this->controller->expects($this->once())
            ->method('notFoundAction')
            ->will($this->returnValue(404));

        $this->assertEquals(404, $this->controller->indexAction());
    }

    public function testIndexActionWithNIAndGoodsLicenceRemovesCorrectFieldsets()
    {
        $this->createMockController(
            [
                'generateForm',
                'getLicenceEntity',
                'getApplicationId',
                'makeRestCall',
                'layout'
            ]
        );

        $this->controller->expects($this->any())
            ->method('getApplicationId')
            ->will($this->returnValue(1));

        $mockStatus = [
            'Results' => ['foo']
        ];
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($mockStatus));

        $licenceData = [
            'niFlag' => true,
            'goodsOrPsv' => 'goods',
            'licenceType' => '1'
        ];
        $this->controller->expects($this->once())
            ->method('getLicenceEntity')
            ->will($this->returnValue($licenceData));

        $mockForm = $this->getMock('\stdClass', ['remove', 'setData']);
        $mockForm->expects($this->at(0))
            ->method('remove')
            ->with('operator-type');
        $mockForm->expects($this->at(1))
            ->method('remove')
            ->with('licence-type-psv');

        $formData = [
            'operator_location' => [
                'operator_location' => 'ni'
            ],
            'operator-type' => [
                'operator-type' => 'goods'
            ],
            'licence-type' => [
                'licence_type' => '1'
            ]
        ];
        $mockForm->expects($this->once())
            ->method('setData')
            ->with($formData);

        $this->controller->expects($this->once())
            ->method('generateForm')
            ->will($this->returnValue($mockForm));

        $this->controller->expects($this->never())
            ->method('layout');

        $this->controller->indexAction();
    }

    public function testIndexActionWithNonNIAndGoodsLicence()
    {
        /**
         * although this test bootstraps quite a bit as
         * per the previous one, it makes less assertions;
         * basically it only cares that the form isn't
         * manipulated (as the NI flag is false)
         */
        $this->createMockController(
            [
                'generateForm',
                'getLicenceEntity',
                'getApplicationId',
                'makeRestCall'
            ]
        );

        $this->controller->expects($this->any())
            ->method('getApplicationId')
            ->will($this->returnValue(1));

        $mockStatus = [
            'Results' => ['foo']
        ];
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($mockStatus));

        $licenceData = [
            'niFlag' => false,
            'goodsOrPsv' => 'goods',
            'licenceType' => '1'
        ];
        $this->controller->expects($this->once())
            ->method('getLicenceEntity')
            ->will($this->returnValue($licenceData));

        $mockForm = $this->getMock('\stdClass', ['remove', 'setData']);
        $mockForm->expects($this->once())
            ->method('remove')
            ->with('licence-type-psv');

        $formData = [
            'operator_location' => [
                'operator_location' => 'uk'
            ],
            'operator-type' => [
                'operator-type' => 'goods'
            ],
            'licence-type' => [
                'licence_type' => '1'
            ]
        ];
        $mockForm->expects($this->once())
            ->method('setData')
            ->with($formData);

        $this->controller->expects($this->once())
            ->method('generateForm')
            ->will($this->returnValue($mockForm));

        $this->controller->indexAction();
    }

    public function testIndexActionWithNonNIAndPsvLicence()
    {
        /**
         * the key assertions in this test revolve around ensuring
         * all licence-type stuff is replaced with licence-type-psv
         * NI/UK is incidental
         */
        $this->createMockController(
            [
                'generateForm',
                'getLicenceEntity',
                'getApplicationId',
                'makeRestCall'
            ]
        );

        $this->controller->expects($this->any())
            ->method('getApplicationId')
            ->will($this->returnValue(1));

        $mockStatus = [
            'Results' => ['foo']
        ];
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($mockStatus));

        $licenceData = [
            'niFlag' => false,
            'goodsOrPsv' => 'psv',
            'licenceType' => '1'
        ];
        $this->controller->expects($this->once())
            ->method('getLicenceEntity')
            ->will($this->returnValue($licenceData));

        $mockForm = $this->getMock('\stdClass', ['remove', 'setData']);
        $mockForm->expects($this->once())
            ->method('remove')
            ->with('licence-type');

        $formData = [
            'operator_location' => [
                'operator_location' => 'uk'
            ],
            'operator-type' => [
                'operator-type' => 'psv'
            ],
            'licence-type-psv' => [
                'licence-type-psv' => '1'
            ]
        ];
        $mockForm->expects($this->once())
            ->method('setData')
            ->with($formData);

        $this->controller->expects($this->once())
            ->method('generateForm')
            ->will($this->returnValue($mockForm));

        $this->controller->indexAction();
    }

    public function testSimpleAction()
    {
        $this->createMockController(
            [
                'generateForm',
                'getLicenceEntity',
                'getApplicationId',
                'makeRestCall',
                'layout'
            ]
        );

        $this->controller->expects($this->any())
            ->method('getApplicationId')
            ->will($this->returnValue(1));

        $mockStatus = [
            'Results' => ['foo']
        ];
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($mockStatus));

        $licenceData = [
            'niFlag' => false,
            'goodsOrPsv' => 'goods',
            'licenceType' => '1'
        ];
        $this->controller->expects($this->once())
            ->method('getLicenceEntity')
            ->will($this->returnValue($licenceData));

        $mockForm = $this->getMock('\stdClass', ['remove', 'setData']);
        $mockForm->expects($this->once())
            ->method('remove')
            ->with('licence-type-psv');

        $formData = [
            'operator_location' => [
                'operator_location' => 'uk'
            ],
            'operator-type' => [
                'operator-type' => 'goods'
            ],
            'licence-type' => [
                'licence_type' => '1'
            ]
        ];
        $mockForm->expects($this->once())
            ->method('setData')
            ->with($formData);

        $this->controller->expects($this->once())
            ->method('generateForm')
            ->will($this->returnValue($mockForm));

        $mockView = $this->getMock('\stdClass', ['setTemplate']);
        $this->controller->expects($this->once())
            ->method('layout')
            ->will($this->returnValue($mockView));

        $mockView->expects($this->once())
            ->method('setTemplate')
            ->with('layout/simple');

        $this->controller->simpleAction();
    }
}

<?php

/**
 * Stay Test Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Olcs\Controller\Cases\Hearing\StayController;
use OlcsTest\Controller\ControllerTestAbstract;

/**
 * Stay Test Controller
 */
class HearingAppealControllerTest extends ControllerTestAbstract
{
    protected $testClass = 'Olcs\Controller\Cases\Hearing\HearingAppealController';

    protected $proxyMethdods = [
        'indexAction' => 'redirectToIndex'
    ];

    public function setUp()
    {
        $this->markTestSkipped();
    }

    /**
     * Isolated test for the redirect action method.
     */
    public function testRedirectToIndex()
    {
        $sut = $this->getMock($this->testClass, ['redirect', 'toRouteAjax']);
        $sut->expects($this->once())
            ->method('redirect')
            ->will($this->returnSelf());

        $sut->expects($this->once())
            ->method('toRouteAjax')

            ->with('case_hearing_appeal', ['action' => 'details', 'id' => null], ['code' => '301'], true)
            ->will($this->returnValue('return'));

        $this->assertEquals('return', $sut->redirectToIndex());
    }
}

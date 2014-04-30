<?php

/**
 * Case Penalty Controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Case Penalty Controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CasePenaltyControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\CasePenaltyController',
            array(
                'getTabInformationArray',
                'getCase',
                'getCaseDetailsArray',
                'getCaseSummaryArray',
                'makeRestCall',
                'generatePenaltyForm',
                'fromRoute',
                'setBreadcrumb',
                'processEdit',
                'processAdd',
                'redirect'
            )
        );

        $this->view = $this->getMock(
            'Zend\View\Model\ViewModel',
            [
                'setVariables',
                'setTemplate'
            ]
        );

        parent::setUp();
    }

    /**
     * @dataProvider indexActionProvider
     *
     * @param int $caseId
     * @param int $licenceId
     * @param array $results
     *
     */
    public function testIndexAction($caseId, $licenceId, $results)
    {
        $this->getFromRoute(0, 'case', $caseId);
        $this->getFromRoute(1, 'licence', $licenceId);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('getTabInformationArray');

        $this->controller->expects($this->once())
            ->method('getCase')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('getCaseDetailsArray')
            ->with($this->equalTo(array()));

        $this->controller->expects($this->once())
            ->method('getCaseSummaryArray')
            ->with($this->equalTo(array()));

        $this->controller->expects($this->once())
            ->method('makeRestCall')->
            will($this->returnValue($results));

        $this->controller->expects($this->once())
            ->method('generatePenaltyForm');


        $this->controller->indexAction();
    }

    /**
     * Data provider for testIndexAction
     *
     * @return array
     */
    public function indexActionProvider()
    {
        return array(
            array(24,7, array(
                    'Count' => 1,
                    'Results' => array(
                        0 => array()
                    )
                )
            ),
            array(24,7,array('Count' => 0))
        );
    }

    /**
     * Tests savePenaltyForm add is called
     *
     * @dataProvider savePenaltyFormAddProvider
     *
     * @param array $data
     */
    public function testSavePenaltyFormAdd($data)
    {
        $redirect = $this->getSaveRedirect($data['case'], $data['licence']);

        $this->controller->expects($this->once())
            ->method('processAdd');

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->savePenaltyForm($data);
    }

    /**
     * Tests savePenaltyForm edit is called
     *
     * @dataProvider savePenaltyFormEditProvider
     *
     * @param array $data
     */
    public function testSavePenaltyFormEdit($data)
    {
        $redirect = $this->getSaveRedirect($data['case'], $data['licence']);

        $this->controller->expects($this->once())
            ->method('processEdit');

         $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

         $this->controller->savePenaltyForm($data);
    }

    /**
     *
     * data provider for testSavePenaltyForm
     *
     * @return array
     */
    public function savePenaltyFormAddProvider()
    {
        return array(
            array(
                array(
                    'case' => 24,
                    'licence' => 7,
                    'notes' => 'test'
                )
            )
        );
    }

    /**
     *
     * data provider for testSavePenaltyForm
     *
     * @return array
     */
    public function savePenaltyFormEditProvider()
    {
        return array(
            array(
                array(
                    'id' => 1,
                    'case' => 24,
                    'licence' => 7,
                    'notes' => 'test'
                )
            )
        );
    }

    /**
     * Generate a fromRoute function call
     *
     * @param int $at
     * @param mixed $with
     * @param mixed $will
     */
    private function getFromRoute($at, $with, $will = false)
    {
        if ($will) {
            $this->controller->expects($this->at($at))
                ->method('fromRoute')
                ->with($this->equalTo($with))
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('fromRoute')
                ->with($this->equalTo($with));
        }
    }

    /**
     * Creates a mock class (used for the redirect method)
     *
     * @param int $caseId
     * @param int $licenceId
     *
     */
    private function getSaveRedirect($caseId, $licenceId)
    {
        $redirect = $this->getMock('stdClass', ['toRoute']);
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with($this->equalTo('case_penalty'), $this->equalTo(array('licence' => $licenceId, 'case' => $caseId)));

        return $redirect;
    }
}

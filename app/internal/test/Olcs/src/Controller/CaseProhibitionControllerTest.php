<?php

/**
 * Case Prohibition Controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Case Prohibition Controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CaseProhibitionControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseProhibitionController',
            array(
                'getTabInformationArray',
                'getCase',
                'getCaseSummaryArray',
                'makeRestCall',
                'generateForm',
                'fromRoute',
                'setBreadcrumb',
                'processEdit',
                'processAdd',
                'redirect',
                'getView'
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
            ->method('getCaseSummaryArray')
            ->with($this->equalTo(array()));

        $this->controller->expects($this->once())
            ->method('makeRestCall')->
            will($this->returnValue($results));

        $form = $this->getMock('stdClass', ['setData']);
        $form->expects($this->once())
             ->method('setData');

        $this->controller->expects($this->once())
            ->method('generateForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setVariables');

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('case/manage');

        $this->assertEquals($this->view, $this->controller->indexAction());
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
                        0 => array(
                            'case' => array(
                                'id' => 24
                            )
                        )
                    )
                )
            ),
            array(24,7,array('Count' => 0))
        );
    }

    /**
     * Tests saveProhibitionForm add is called
     *
     * @param array $data
     */
    public function testSaveProhibitionFormAddSubmit()
    {
        $data = array(
            'case' => 24,
            'notes' => 'test',
            'submit' => '',
            'cancel' => null
        );

        $redirect = $this->getSaveRedirect();

        $this->controller->expects($this->once())
            ->method('processAdd');

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->saveProhibitionForm($data);
    }

    /**
     * Tests saveProhibitionForm edit is called when submit pressed
     *
     * @dataProvider saveProhibitionFormEditSubmitProvider
     *
     * @param array $data
     */
    public function testSaveProhibitionFormEditSubmit($data)
    {
        $redirect = $this->getSaveRedirect();

        $this->controller->expects($this->once())
            ->method('processEdit');

         $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

         $this->controller->saveProhibitionForm($data);
    }

    /**
     *
     * data provider for testSaveProhibitionSubmitForm
     *
     * @return array
     */
    public function saveProhibitionFormEditSubmitProvider()
    {
        return array(
            array(
            array(
                    'id' => 1,
                    'case' => 24,
                    'notes' => 'test',
                    'submit' => '',
                    'cancel' => null
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
     */
    private function getSaveRedirect()
    {
        $redirect = $this->getMock('stdClass', ['toRoute']);
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with(
                $this->equalTo('case_prohibition'),
                $this->equalTo(array()),
                $this->equalTo(array()),
                $this->equalTo(true)
            );

        return $redirect;
    }
}

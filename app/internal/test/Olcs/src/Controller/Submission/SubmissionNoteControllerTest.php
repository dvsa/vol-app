<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc <michael.cooper@valtech.co.uk>
 */
namespace OlcsTest\Controller\Submission;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Search controller form post tests
 *
 * @author adminmwc <michael.cooper@valtech.co.uk>
 */
class SubmissionNoteControllerTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionNoteController', array(
            'getServiceLocator',
            'setBreadcrumb',
            'redirect',
            'makeRestCall',
            'generateNoteForm',
            'processAdd',
            'getViewModel',
            'getRequest',
            'getLoggedInUser',
            'generateForm',
            )
        );
        $this->controller->routeParams = array(
            'case' => 54,
            'licence' => 7,
            'typeId' => 12,
            'type' => 'submission',
            'section' => 'case-summary-info',
            'action' => 'add');
        $this->licenceData = array(
            'id' => 7,
            'licenceType' => 'Standard National',
            'goodsOrPsv' => 'Psv'
        );

        parent::setUp();
        $_POST = array();
    }

    public function testAddAction()
    {

        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(
                array(
                    'licence_case_list/pagination' => array('licence' => $this->controller->routeParams['licence']),
                    'case_manage' => array(
                        'case' => $this->controller->routeParams['case'],
                        'licence' => $this->controller->routeParams['licence'],
                        'tab' => 'overview'
                    ),
                    'submission' => array(
                        'case' => $this->controller->routeParams['case'],
                        'licence' => $this->controller->routeParams['licence'],
                        'id' => $this->controller->routeParams['typeId'],
                        'action' => 'edit'
                    )
                )
            );

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with(
                $this->equalTo('Submission'), $this->equalTo('GET'), $this->equalTo(
                    array('id' => $this->controller->routeParams['typeId'])
                )
            )
            ->will($this->returnValue(array('id' => $this->controller->routeParams['typeId'], 'version' => 1)));

        $this->controller->expects($this->once())
            ->method('generateNoteForm')
            ->with(array('version' => 1), 'processNote')
            ->will($this->returnValue('form'));

        $viewModel = $this->getMock('\stdClass', array('setTemplate'));

        $viewModel->expects($this->once())
            ->method('setTemplate')
            ->with('form');

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->with(
                array(
                    'form' => 'form',
                    'params' => array(
                        'pageTitle' => 'add-submission-note',
                        'pageSubTitle' => array('add-submission-note-text', 'case-summary-info'),
                    )
                )
            )
            ->will($this->returnValue($viewModel));

        $this->controller->addAction();
    }

    public function testCancelButton()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionNoteController', array(
                'setBreadcrumb',
                'backToSubmissionButton'
            )
        );
        $this->controller->routeParams = array(
            'case' => 54,
            'licence' => 7,
            'typeId' => 12,
            'type' => 'submission',
            'action' => 'add'
        );
        $_POST['cancel-note'] = '';

        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(
                array(
                    'licence_case_list/pagination' => array('licence' => $this->controller->routeParams['licence']),
                    'case_manage' => array(
                        'case' => $this->controller->routeParams['case'],
                        'licence' => $this->controller->routeParams['licence'],
                        'tab' => 'overview'
                    ),
                    'submission' => array(
                        'case' => $this->controller->routeParams['case'],
                        'licence' => $this->controller->routeParams['licence'],
                        'id' => $this->controller->routeParams['typeId'],
                        'action' => 'edit'
                    )
                )
            );

        $this->controller->expects($this->once())
            ->method('backToSubmissionButton');

        $this->controller->addAction();
    }

    public function testBackToSubmissionButton()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionNoteController', array(
            'redirect'
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'typeId' => 12);

        $redirect = $this->getMock('\stdClass', array('toRoute'));

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('submission', array('case' => 54, 'licence' => 7, 'id' => 12, 'action' => 'edit'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->backToSubmissionButton();
    }

    public function testGenerateNoteForm()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionNoteController', array(
            'generateForm',
            )
        );

        $form = $this->getMock('\stdClass', array('setData'));

        $form->expects($this->once())
            ->method('setData')
            ->with(array('id' => 1));

        $this->controller->expects($this->once())
            ->method('generateForm')
            ->with('note', 'processNote')
            ->will($this->returnValue($form));

        $this->controller->generateNoteForm(array('id' => 1), 'processNote');
    }

    public function testCreateNote()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionNoteController', array(
            'makeRestCall',
            'params',
            'getLoggedInUser'
            )
        );
        $this->controller->routeParams = array('section' => 'case-summary-info');

        $params = $this->getMock('\stdClass', array('fromPost'));

        $params->expects($this->once())
            ->method('fromPost')
            ->will(
                $this->returnValue(
                    array(
                        'main' => array('note' => 'This is a new note')
                    )
                )
        );

        $this->controller->expects($this->atLeastOnce())
            ->method('params')
            ->will($this->returnValue($params));

        $this->controller->expects($this->once())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with(
                $this->equalTo('User'), $this->equalTo('GET'), $this->equalTo(
                    array('id' => 1)
                )
            )
            ->will($this->returnValue(array('id' => 1, 'version' => 1, 'name' => 'Ken Dod')));

        $methodData = array(
            'case-summary-info' => array(
                'notes' => array()
            )
        );
        $this->controller->createNote($methodData);
    }

    public function testProcessNote()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionNoteController', array(
            'makeRestCall',
            'params',
            'createNote',
            'processEdit',
            'redirect'
            )
        );
        $this->controller->routeParams = array(
            'licence' => 7,
            'case' => 54,
            'id' => 23,
            'action' => 'edit',
            'type' => 'submission',
            'typeId' => 23);

        $params = $this->getMock('\stdClass', array('fromPost'));

        $params->expects($this->once())
            ->method('fromPost')
            ->will(
                $this->returnValue(
                    array('version' => 1)
                )
        );

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($params));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with(
                $this->equalTo($this->controller->routeParams['type']), $this->equalTo('GET'), $this->equalTo(
                    array('id' => $this->controller->routeParams['typeId'])
                )
            )
            ->will(
                $this->returnValue(
                    array(
                        'id' => 1,
                        'text' => '{}'
                    )
                )
        );

        $this->controller->expects($this->once())
            ->method('createNote')
            ->with(array())
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('processEdit')
            ->with(
                array('id' => 1, 'version' => 1, 'text' => '[]'), $this->controller->routeParams['type']
        );

        $redirect = $this->getMock('\stdClass', array('toRoute'));

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('submission', $this->controller->routeParams);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processNote(array());
    }
}

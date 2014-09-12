<?php
/**
 * Licence Processing Note controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller\Licence;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Licence Processing Note controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingNoteControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Licence\Processing\LicenceProcessingNoteController',
            array(
                'makeRestCall',
                'getLoggedInUser',
                'getTable',
                'generateFormWithData',
                'getFromRoute',
                'getFromPost',
                'getView',
                'url',
                'renderView',
                'redirectToRoute',
                'processAdd',
                'processEdit',

            )
        );

        $this->view = $this->getMock(
            '\Zend\View\Model\ViewModel',
            array(
                'setTemplate',
            )
        );

        parent::setUp();
    }

    public function testIndexAction()
    {
        $licenceId = 7;
        $page = 1;
        $sort = 'priority';
        $order = 'desc';
        $limit = 10;

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromPost(1, 'action', null);
        $this->getFromPost(2, 'id', null);
        $this->getFromRouteWithDefault(3, 'page', $page, $page);
        $this->getFromRouteWithDefault(4, 'sort', $sort, $sort);
        $this->getFromRouteWithDefault(5, 'order', $order, $order);
        $this->getFromRouteWithDefault(6, 'limit', $limit, $limit);

        $this->controller->expects($this->once())
            ->method('url');

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($this->getSampleResult()));

        //$this->controller->expects($this->once())
          //  ->method('appendLinkedId');

        $this->controller->expects($this->once())
            ->method('getTable');

        $this->controller->expects($this->once())
            ->method('getView')
        ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate');

        $this->controller->expects($this->once())
            ->method('renderView');

        $this->controller->indexAction();
    }

    /**
     * Tests for a crud add redirect from index action
     */
    public function testIndexActionAddRedirect()
    {
        $licenceId = 7;
        $action = 'Add';
        $id = null;
        $route = 'licence/processing/add-note';

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromPost(1, 'action', $action);
        $this->getFromPost(2, 'id', $id);

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo($route),
                $this->equalTo(
                    [
                        'action' => strtolower($action),
                        'licence' => $licenceId,
                        'noteType' => 'note_t_lic',
                        'linkedId' => $licenceId]
                ),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->indexAction();
    }

    /**
     * Tests for a crud edit/delete redirect from index action
     *
     * @dataProvider indexActionModifyRedirectProvider
     *
     * @param string $action
     */
    public function testIndexActionModifyRedirect($action)
    {
        $licenceId = 7;
        $id = 1;
        $route = 'licence/processing/modify-note';

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromPost(1, 'action', $action);
        $this->getFromPost(2, 'id', $id);

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo($route),
                $this->equalTo(['action' => strtolower($action), 'id' => $id]),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->indexAction();
    }

    public function indexActionModifyRedirectProvider()
    {
        return [
            ['Edit', 'Delete']
        ];
    }

    public function testAddAction()
    {
        $licenceId = 7;
        $noteType = 'note_t_lic';
        $linkedId = 1;

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'noteType', $noteType);
        $this->getFromRoute(2, 'linkedId', $linkedId);

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('licence/processing/notes/form');

        $this->controller->expects($this->once())
            ->method('renderView')
            ->with($this->equalTo($this->view));

        $this->controller->addAction();
    }

    public function testEditAction()
    {
        $id = 1;
        $note = [
            'comment' => 'comment',
            'id' => $id,
            'priority' => 'Y',
            'version' => 1
        ];

        $this->getFromRoute(0, 'id', $id);

        $this->controller->expects($this->once())
            ->method('makeRestCall')
        ->will($this->returnValue($note));

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
        ->will($this->returnValue($this->getEditForm()));

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate');

        $this->controller->expects($this->once())
            ->method('renderView')
            ->with($this->equalTo($this->view));

        $this->controller->editAction();
    }

    /**
     * Tests the process add notes function
     *
     * @dataProvider processAddNotesProvider
     *
     * @param array $data
     */
    public function testProcessAddNotes($data)
    {
        $this->controller->expects($this->once())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->once())
            ->method('processAdd')
            ->will($this->returnValue(['id' => 1]));

        $this->getRedirectToIndex();

        $this->controller->processAddNotes($data);
    }

    /**
     * Tests the process add notes function redirects properly on failure
     *
     * @dataProvider processAddNotesProvider
     *
     * @param array $data
     */
    public function testProcessAddNotesFail($data)
    {
        $this->controller->expects($this->once())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->once())
            ->method('processAdd')
            ->will($this->returnValue([]));

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('licence/processing/add-note'),
                $this->equalTo(['action' => 'Add']),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->processAddNotes($data);
    }

    /**
     * Tests the process add notes function throws a bad request
     * exception when the linkedId is missing
     *
     * @expectedException \Common\Exception\BadRequestException
     */
    public function testProcessAddNotesMissingLinkException()
    {
        $data = $this->getTestFormPost('note_t_app', null, null, null);

        $this->controller->expects($this->once())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->processAddNotes($data);
    }

    /**
     * Data provider for process add notes
     */
    public function processAddNotesProvider()
    {
        return [
            [$this->getTestFormPost('note_t_lic'), 'licence'],
            [$this->getTestFormPost('note_t_app'), 'application'],
            [$this->getTestFormPost('note_t_irfo_gv'), 'irfoGvPermit'],
            [$this->getTestFormPost('note_t_irfo_psv'), 'irfoPsvAuth'],
            [$this->getTestFormPost('note_t_case'), 'case'],
            [$this->getTestFormPost('note_t_bus'), 'busReg']
        ];
    }

    public function testProcessEditNotes()
    {
        $data = $this->getTestFormPost('', 1, 1);

        $this->controller->expects($this->once())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->once())
            ->method('processEdit')
            ->will($this->returnValue([]));

        $this->getRedirectToIndex();

        $this->controller->processEditNotes($data);
    }

    public function testProcessEditNotesFail()
    {
        $data = $this->getTestFormPost('', 1, 1);

        $this->controller->expects($this->once())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->once())
            ->method('processEdit')
            ->will($this->returnValue(['failed']));

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('licence/processing/modify-note'),
                $this->equalTo(['action' => 'Edit']),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->processEditNotes($data);
    }

    /**
     * Tests the output of getIdField()
     *
     * @dataProvider getIdFieldProvider
     *
     * @param $refDataKey
     * @param $expectedOutput
     */
    public function testGetIdField($refDataKey, $expectedOutput)
    {
        $this->assertEquals($this->controller->getIdField($refDataKey), $expectedOutput);
    }

    /**
     * Data provider got testGetIdField
     *
     * @return array
     */
    public function getIdFieldProvider()
    {
        return [
            ['note_t_lic', 'licence'],
            ['note_t_app', 'application'],
            ['note_t_irfo_gv', 'irfoGvPermit'],
            ['note_t_irfo_psv', 'irfoPsvAuth'],
            ['note_t_case', 'case'],
            ['note_t_bus', 'busReg']
        ];
    }

    public function testDeleteServiceName()
    {
        $this->assertEquals('Note', $this->controller->getDeleteServiceName());
    }

    public function getRedirectToIndex()
    {
        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('licence/processing/notes'),
                $this->equalTo([]),
                $this->equalTo([]),
                $this->equalTo(true)
            );
    }

    public function getTestFormPost($refDataNoteType, $id = null, $version = null, $linkedId = 1)
    {
        return [
            'main' => [
                'comment' => 'the comment',
                'priority' => 'Y'
            ],
            'id' => $id,
            'licence' => 7,
            'noteType' => $refDataNoteType,
            'linkedId' => $linkedId,
            'version' => $version
        ];
    }

    private function getSampleResult()
    {
        return [
            'Results' => [
                0 => [
                    'noteType' => [
                        'id' => 'note_t_lic',
                        'description' => 'Licence'
                    ]
                ]
            ]
        ];
    }

    private function getEditForm()
    {
        $formMock = $this->getMock('stdClass', ['get']);
        $getElementMock = $this->getMock('stdClass', ['get']);
        $setAttributeMock = $this->getMock('stdClass', ['setAttribute']);

        $setAttributeMock->expects($this->once())
            ->method('setAttribute');

        $getElementMock->expects($this->once())
            ->method('get')
            ->with('comment')
            ->will($this->returnValue($setAttributeMock));

        $formMock->expects($this->once())
            ->method('get')
            ->with('main')
            ->will($this->returnValue($getElementMock));

        return $formMock;
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
                ->method('getFromRoute')
                ->with($this->equalTo($with))
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('getFromRoute')
                ->with($this->equalTo($with));
        }
    }

    /**
     * Generate a fromRoute function call
     *
     * @param int $at
     * @param mixed $with
     * @param mixed $default
     * @param mixed $will
     */
    private function getFromRouteWithDefault($at, $with, $default, $will = false)
    {
        if ($will) {
            $this->controller->expects($this->at($at))
                ->method('getFromRoute')
                ->with(
                    $this->equalTo($with),
                    $this->equalTo($default)
                )
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('getFromRoute')
                ->with(
                    $this->equalTo($with),
                    $this->equalTo($default)
                );
        }
    }

    /**
     * Generate a fromPost function call
     *
     * @param int $at
     * @param mixed $with
     * @param mixed $will
     */
    private function getFromPost($at, $with, $will = false)
    {
        if ($will) {
            $this->controller->expects($this->at($at))
                ->method('getFromPost')
                ->with($this->equalTo($with))
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('getFromPost')
                ->with($this->equalTo($with));
        }
    }
}

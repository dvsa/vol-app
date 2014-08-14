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
class CaseRevokeControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );

        parent::setUp();
    }

    public function testIndexAction()
    {
        $params = array('action' => 'edit', 'licence' => 'b', 'case' => 1234, 'id' => 123);
        $revokes = ['Results'=> [0=>['id'=>1231]]];
        $variables = array(
            'tab' => 'revoke',
            'revoke' => isset($revokes['Results'][0]) ? $revokes['Results'][0] : null
        );

        /* $form = $this->getMock('\Zend\Form\Form', ['setData']);
        $form->expects($this->atLeastOnce())->method('setData'); */

        $controller = $this->getController(
            ['setBreadcrumbRevoke', 'getParams', 'getRevokes', 'getCaseVariables']
        );

        $controller->expects($this->once())->method('setBreadcrumbRevoke')->will($this->returnValue(null));
        $controller->expects($this->any())->method('getRevokes')->will($this->returnValue($revokes));
        $controller->expects($this->once())->method('getCaseVariables');
        $controller->expects($this->once())
                   ->method('getParams')
                   ->with($this->equalTo(['action', 'licence', 'case', 'id']))
                   ->will($this->returnValue($params));
        $controller->expects($this->once())->method('getCaseVariables')->with(1234, $variables);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $controller->indexAction());
    }

    public function testGetRevokes()
    {
        $caseId = 1234;
        $return = rand();

        $controller = $this->getController(['makeRestCall']);
        $controller->expects($this->once())
                   ->method('makeRestCall')
                   ->will($this->returnValue($return));

        $this->assertEquals($return, $controller->getRevokes($caseId));
    }

    public function testGetRevoke()
    {
        $revokeId = 1234;
        $return = rand();

        $controller = $this->getController(['makeRestCall']);
        $controller->expects($this->once())
                   ->method('makeRestCall')
                   ->will($this->returnValue($return));

        $this->assertEquals($return, $controller->getRevoke($revokeId));
    }

    public function testAddAction()
    {
        $params = array('action' => 'edit', 'licence' => 'b', 'case' => 'c', 'id' => 123);

        $form = $this->getMock('\Zend\Form\Form', ['setData']);
        $form->expects($this->atLeastOnce())->method('setData');

        $controller = $this->getController(
            ['checkCancel', 'setBreadcrumbRevoke', 'getParams', 'getRevoke', 'formatDataForForm', 'generateForm']
        );

        $controller->expects($this->once())->method('checkCancel')->will($this->returnValue(null));
        $controller->expects($this->once())->method('setBreadcrumbRevoke')->will($this->returnValue(null));
        $controller->expects($this->once())
                   ->method('getParams')
                   ->with($this->equalTo(['action', 'licence', 'case', 'id']))
                   ->will($this->returnValue($params));
        $controller->expects($this->any())->method('getRevoke')->will($this->returnValue([]));
        $controller->expects($this->any())->method('formatDataForForm')->will($this->returnValue([]));
        $controller->expects($this->once())
                   ->method('generateForm')
                   ->with($this->equalTo('revoke'), $this->equalTo('processRevoke'))
                   ->will($this->returnValue($form));

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $controller->addAction());
    }

    public function testCheckCancel()
    {
        global $_POST;
        $_POST['cancel-revoke'] = 'yep';

        $params = array('action' => 'edit', 'licence' => 'b', 'case' => 'c', 'id' => 123);

        $controller = $this->getController(['getParams']);
        $controller->expects($this->once())
                   ->method('getParams')
                   ->with($this->equalTo(['action', 'licence', 'case', 'id']))
                   ->will($this->returnValue($params));

        $controller->checkCancel();
    }

    public function testSetBreadcrumbRevoke()
    {
        $params = array('action' => 'edit', 'licence' => 'b', 'case' => 'c', 'id' => 123);

        $controller = $this->getController(['getParams', 'setBreadcrumb']);

        $controller->expects($this->once())
                   ->method('getParams')
                   ->with($this->equalTo(['action', 'licence', 'case', 'id']))
                   ->will($this->returnValue($params));

        $controller->expects($this->once())
                   ->method('setBreadcrumb');

        $this->assertNull($controller->setBreadcrumbRevoke());
    }

    /**
     * @dataProvider dpTestArrayToId
     */
    public function testArrayToId($in, $out)
    {
        $controller = $this->getController([]);
        $this->assertEquals($out, $controller->arrayToId($in));
    }

    public function dpTestArrayToId()
    {
        return array(
            array(['id' => '1234', 'some-key' => 'some-value'], '1234'),
            array(['some-key' => 'some-value'], null),
        );
    }

    /**
     * tests that the edit action simply recirects to the add action.
     */
    public function testEditAction()
    {
        $controller = $this->getController(['addAction']);
        $controller->expects($this->once())
                   ->method('addAction')
                   ->will($this->returnValue('ABC123'));

        $this->assertEquals('ABC123', $controller->editAction());
    }

    /**
     * @dataProvider licenceTypeDataProvider
     */
    public function testGenerateForm($licenceType, $niFlag)
    {
        $formName = 'form';
        $callback = 'myCallback';

        $getPiReasonsNvpArray = [rand(), rand()];
        $getPresidingTcArray = [rand(), rand()];

        $formGetPlugin = $this->getMock('\stdClass', ['setValueOptions']);
        $formGetPlugin->expects($this->at(0))
                      ->method('setValueOptions')
                      ->with($this->equalTo($getPiReasonsNvpArray))
                      ->will($this->returnValue(null));
        $formGetPlugin->expects($this->at(1))
                      ->method('setValueOptions')
                      ->with($this->equalTo($getPresidingTcArray))
                      ->will($this->returnValue(null));

        $form = $this->getMock('\stdClass', ['get']);
        $form->expects($this->at(0))->method('get')->with('main')
             ->will($this->returnSelf());
        $form->expects($this->at(1))->method('get')->with('piReasons')
             ->will($this->returnValue($formGetPlugin));
        $form->expects($this->at(2))->method('get')->with('main')
             ->will($this->returnSelf());
        $form->expects($this->at(3))->method('get')->with('presidingTc')
             ->will($this->returnValue($formGetPlugin));

        $controller = $this->getController(
            ['getForm', 'getPiReasonsNvpArray', 'getPresidingTcArray', 'formPost', 'fromRoute', 'makeRestCall']
        );
        $controller->expects($this->once())
                   ->method('fromRoute')
                   ->with($this->equalTo('licence'))
                   ->will($this->returnValue(7));
        $controller->expects($this->once())
                   ->method('makeRestCall')
                   ->will(
                       $this->returnValue(
                           [
                               'goodsOrPsv' => $licenceType,
                               'niFlag' => $niFlag
                           ]
                       )
                   );
        $controller->expects($this->once())
                   ->method('getForm')
                   ->with($this->equalTo($formName))
                   ->will($this->returnValue($form));
        $controller->expects($this->once())
                   ->method('getPiReasonsNvpArray')
                ->with($licenceType)
                   ->will($this->returnValue($getPiReasonsNvpArray));
        $controller->expects($this->once())
                   ->method('getPresidingTcArray')
                   ->will($this->returnValue($getPresidingTcArray));
        $controller->expects($this->once())
                   ->method('formPost')
                   ->with($this->equalTo($form), $this->equalTo($callback))
                   ->will($this->returnArgument(0));

        $this->assertEquals($form, $controller->generateForm($formName, $callback));
    }

    public function licenceTypeDataProvider ()
    {
        return [
            array('goods', 'GV', 1),
            array('psv', 'PSV', 1),
            array('goods', 'GV', 0)
        ];
    }

    /**
     * @dataProvider licenceTypeDataProvider
     */
    public function testGetPiReasonsNvpArray($licenceType, $shortLicenceType, $niFlag)
    {
        $pi = array(
            'Results' => array(
                array('id' => 1, 'sectionCode' => 'sc 1', 'description' => 'desc 1'),
                array('id' => 2, 'sectionCode' => 'sc 2', 'description' => 'desc 2'),
                array('id' => 3, 'sectionCode' => 'sc 3', 'description' => 'desc 3'),
                array('id' => 4, 'sectionCode' => 'sc 4', 'description' => 'desc 4'),
            )
        );

        $return = array(
            1 => 'sc 1 - desc 1',
            2 => 'sc 2 - desc 2',
            3 => 'sc 3 - desc 3',
            4 => 'sc 4 - desc 4'
        );

        $controller = $this->getController(['getParams', 'makeRestCall']);
        $controller->expects($this->once())
                   ->method('makeRestCall')
                   ->with(
                       $this->equalTo('Reason'),
                       $this->equalTo('GET'),
                       $this->equalTo(
                           [
                                'isProposeToRevoke' => '1',
                                'goodsOrPsv' => $shortLicenceType,
                                'isNi' => $niFlag,
                                'limit' => 'all'
                           ]
                       )
                   )
                   ->will($this->returnValue($pi));

        $this->assertEquals($return, $controller->getPiReasonsNvpArray($licenceType, $niFlag));
    }

    /**
     * Test
     */
    public function testGetPresidingTcArray()
    {
        $tc = array(
            'Results' => array(
                array('id' => 1, 'name' => 'name 1'),
                array('id' => 2, 'name' => 'name 2'),
                array('id' => 3, 'name' => 'name 3'),
                array('id' => 4, 'name' => 'name 4'),
            )
        );

        $return = array(
            1 => 'name 1',
            2 => 'name 2',
            3 => 'name 3',
            4 => 'name 4'
        );

        $controller = $this->getController(['getParams', 'makeRestCall']);
        $controller->expects($this->once())
                   ->method('makeRestCall')
                   ->with($this->equalTo('PresidingTc'), $this->equalTo('GET'), $this->equalTo([]))
                   ->will($this->returnValue($tc));

        $this->assertEquals($return, $controller->getPresidingTcArray());
    }

    /**
     * Test
     */
    public function testDeleteAction()
    {
        $params = array('action' => 'a', 'licence' => 'b', 'case' => 'c', 'id' => 123);

        $controller = $this->getController(['getParams', 'makeRestCall']);

        $controller->expects($this->once())
                   ->method('getParams')
                   ->with($this->equalTo(array('action', 'licence', 'case', 'id')))
                   ->will($this->returnValue($params));

        $controller->expects($this->once())
                   ->method('makeRestCall')
                   ->with($this->equalTo('Revoke'), $this->equalTo('DELETE'), $this->equalTo(['id'=>123]))
                   ->will($this->returnValue(null));

        $this->assertNull($controller->deleteAction());
    }

    /**
     * Test
     */
    public function testProcessRevokeAdd()
    {
        $data = ['id' => '', 'something-else' => 'value'];
        $params = array('action' => 'a', 'licence' => 'b', 'case' => 'c');

        $controller = $this->getController(['getParams', 'processAdd']);

        $controller->expects($this->once())
                   ->method('getParams')
                   ->with($this->equalTo(array('action', 'licence', 'case')))
                   ->will($this->returnValue($params));

        $controller->expects($this->once())
                   ->method('processAdd')
                   ->with($this->equalTo($data), $this->equalTo('Revoke'))
                   ->will($this->returnValue(null));

        $this->assertNull($controller->processRevoke($data));
    }

    /**
     * Test
     */
    public function testProcessRevokeEdit()
    {
        $data = ['cancel-revoke' => 'value', 'id' => 1, 'something-else' => 'value'];
        $processData = $data;
        unset($processData['cancel-revoke']);
        $params = array('action' => 'a', 'licence' => 'b', 'case' => 'c');

        $controller = $this->getController(['getParams', 'processEdit']);

        $controller->expects($this->once())
                   ->method('getParams')
                   ->with($this->equalTo(array('action', 'licence', 'case')))
                   ->will($this->returnValue($params));

        $controller->expects($this->once())
                   ->method('processEdit')
                   ->with($this->equalTo($processData), $this->equalTo('Revoke'))
                   ->will($this->returnValue(null));

        $this->assertNull($controller->processRevoke($data));
    }

    /**
     * Gets a new mocked instance of the controller.
     *
     * @param array $mockedMethods
     *
     * @return \Olcs\Controller\CaseRevokeController
     */
    public function getController($mockedMethods = array())
    {
        $methods = array_merge(['getView', 'redirect'], $mockedMethods);

        $mock = $this->getMock(
            '\Olcs\Controller\CaseRevokeController',
            $methods
        );

        $mock->expects($this->any())->method('getView')->will($this->returnValue($this->getView()));
        $mock->expects($this->any())->method('redirect')->will($this->returnValue($this->getRedirect()));

        return $mock;
    }

    /**
     * Gets a mocked instance of the view.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getView()
    {
        return $this->getMock('\Zend\View\Model\ViewModel', ['setVariables', 'setTemplate']);
    }

    /**
     * Gets a mocked redirect class.
     *
     * @return \stdClass
     */
    public function getRedirect()
    {
        return $this->getMock('\stdClass', ['toRoute']);
    }
}

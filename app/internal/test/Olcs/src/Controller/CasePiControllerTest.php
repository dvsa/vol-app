<?php

/**
 * Public Inquiry Controller tests
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Mvc\Controller\Plugin\Redirect as Redirect;

/**
 * Public Inquiry Controller tests
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class CasePiControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );

        parent::setUp();
    }

    /**
     * We have slightly modified this method. So it's quite important that
     * we test the new intended functionality. We now attempt to get the
     * data from the entity and prefil the form before returning it.
     */
    public function testGenerateFormWithData()
    {
        return $this->markTestSkipped();

        $id = 1;
        $formname = 'formName';
        $callback = 'callbackMethodName';
        $inputData = ['id' => '2', 'name' => 'anem2', 'main' => ['id' => '2', 'name' => 'anem2']];
        $entityData = ['id' => '1', 'pi' => 'pi', 'main' => ['id' => '1', 'pi' => 'pi']];

        $dataSum = array_merge_recursive($entityData, $inputData);

        $form = $this->getMock('Zend\Form\Form', ['setData']);
        $form->expects($this->once())
            ->method('setData')
            ->with($this->equalTo($dataSum))
            ->will($this->returnSelf());

        $request = $this->getMock(
            'Zend\Stdlib\RequestInterface',
            ['isPost', 'setMetadata', 'getMetadata', 'setContent', 'getContent']
        );
        $request->expects($this->once())->method('isPost')->will($this->returnValue(false));



        $sut = $this->getControllerToTest(
            ['generateForm', 'getRequest', 'params', 'load']
        );

        // Form mock
        $sut->expects($this->any())->method('generateForm')
            ->with($formname, $callback, false)
            ->will($this->returnValue($form));

        // Request Mock
        $sut->expects($this->any())->method('getRequest')->will($this->returnValue($request));

        // fromRoute Mock
        $sut->expects($this->any())->method('fromRoute')
            ->with($this->equalTo('id'))
            ->will($this->returnValue($id));

        $params = $this->getMock('stdClass', ['fromRoute']);
        $params->expects($this->any())->method('fromRoute')
               ->with($this->equalTo('id'))
               ->will($this->returnValue($id));

        $sut->expects($this->any())
            ->method('params')
            ->will($this->returnValue($params));

        // Load Mock
        $sut->expects($this->any())->method('load')
            ->with($this->equalTo($id))
            ->will($this->returnValue($entityData));

        $this->assertSame($form, $sut->generateFormWithData($formname, $callback, $inputData, false));
    }

    /**
     * Tests the add/edit action which takes care of dispatching the
     * method for the current section.
     */
    public function testAddEditAction()
    {
        $sut = $this->getControllerToTest(['fromRoute', 'testSectionMethod']);
        $sut->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('section'))
            ->will($this->returnValue('testSectionMethod'));

        $sut->expects($this->once())
            ->method('testSectionMethod')
            ->will($this->returnValue('returnSectionMethod'));

        $this->assertSame('returnSectionMethod', $sut->addEditAction());
    }

    /**
     * Creates a controller instance for a test.
     *
     * @param array $methods
     * @return \Olcs\Controller\CasePiController
     */
    public function getControllerToTest(array $methods)
    {
        $baseMethods = [
            'redirect',
            'getView'
        ];

        $methods = array_merge($methods, $baseMethods);

        $controller = $this->getMock(
            '\Olcs\Controller\CasePiController',
            $methods
        );

        $view = $this->getMock(
            'Zend\View\Model\ViewModel',
            [
                'setVariables',
                'setTemplate'
            ]
        );

        $mockRedirect = $this->getMock(get_class(new Redirect()), ['toRoute', 'toUrl']);

        $controller->expects($this->any())
            ->method('getView')
            ->will($this->returnValue($view));
        $controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        return $controller;
    }
}

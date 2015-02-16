<?php

/**
 * Bus Details Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller\Bus\Details;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Bus Details Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            '\Olcs\Controller\Bus\Details\BusDetailsController',
            array(
                'getViewWithBusReg',
                'renderView',
                'redirectToRoute',
                'isFromEbsr'
            )
        );

        $this->form = $this->getMock(
            '\Zend\Form\Form',
            array(
                'remove',
                'get'
            )
        );

        $this->view = $this->getMock(
            '\Zend\View\Model\ViewModel',
            array(
                'setTemplate'
            )
        );

        parent::setUp();
    }

    /**
     * Tests alter form before validation function
     * when record is not from Ebsr
     */
    public function testAlterFormBeforeValidationNotEbsr()
    {
        $form = $this->form;

        $this->controller->expects($this->once())
            ->method('isFromEbsr')
            ->will($this->returnValue(false));

        $form->expects($this->never())
            ->method('get');

        $form->expects($this->never())
            ->method('remove');

        $this->controller->alterFormBeforeValidation($form);
    }

    /**
     * Tests alter form before validation function
     * when record has come from Ebsr
     */
    public function testAlterFormBeforeValidationWhenFromEbsr()
    {
        $form = $this->form;

        $this->controller->disableFormFields = array(
            'fieldName'
        );

        $fields = $this->getMock(
            '\Zend\Form\Fieldset',
            array(
                'get'
            )
        );

        $attributeMock = $this->getMock(
            '\Zend\Form\Element',
            array(
                'setAttribute'
            )
        );

        $attributeMock->expects($this->any())
            ->method('setAttribute')
            ->with(
                $this->equalTo('disabled'),
                $this->equalTo('disabled')
            );

        $fields->expects($this->any())
            ->method('get')
            ->will($this->returnValue($attributeMock));

        $this->controller->expects($this->once())
            ->method('isFromEbsr')
            ->will($this->returnValue(true));

        $form->expects($this->once())
            ->method('get')
            ->with('fields')
            ->will($this->returnValue($fields));

        $form->expects($this->once())
            ->method('remove');

        $this->controller->alterFormBeforeValidation($form);
    }

    public function testRedirectToIndex()
    {
        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo(null),
                $this->equalTo(['action'=>'edit']),
                $this->equalTo(['code' => '303']),
                $this->equalTo(true)
            );

        $this->controller->redirectToIndex();
    }
}

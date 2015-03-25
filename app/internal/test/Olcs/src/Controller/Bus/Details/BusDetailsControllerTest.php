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
                'isFromEbsr',
                'isLatestVariation'
            )
        );

        $this->form = $this->getMock(
            '\Zend\Form\Form',
            array(
                'setOption'
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
     * when record is not from Ebsr and is the latest variation
     */
    public function testAlterFormBeforeValidationLatestAndNotEbsr()
    {
        $form = $this->form;

        $this->controller->expects($this->once())
            ->method('isLatestVariation')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('isFromEbsr')
            ->will($this->returnValue(false));

        $form->expects($this->never())
            ->method('setOption')
            ->with('readonly', true);

        $this->controller->alterFormBeforeValidation($form);
    }

    /**
     * Tests alter form before validation function
     * when record has come from Ebsr
     */
    public function testAlterFormBeforeValidationWhenFromEbsr()
    {
        $form = $this->form;

        $this->controller->expects($this->once())
            ->method('isFromEbsr')
            ->will($this->returnValue(true));

        $this->controller->expects($this->never())
            ->method('isLatestVariation');

        $form->expects($this->once())
            ->method('setOption')
            ->with('readonly', true);

        $this->controller->alterFormBeforeValidation($form);
    }

    /**
     * Tests alter form before validation function
     * when record is not the latest variation
     */
    public function testAlterFormBeforeValidationWhenNotLatestVariation()
    {
        $form = $this->form;

        $this->controller->expects($this->once())
            ->method('isFromEbsr')
            ->will($this->returnValue(false));

        $this->controller->expects($this->once())
            ->method('isLatestVariation')
            ->will($this->returnValue(false));

        $form->expects($this->once())
            ->method('setOption')
            ->with('readonly', true);

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

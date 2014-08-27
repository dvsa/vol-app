<?php

/**
 * Payment Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsCommonTest\Controller\Application\OperatingCentres;

use CommonTest\Controller\Application\PaymentSubmission\PaymentControllerTest as AbstractPaymentControllerTest;
/**
 * Payment Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PaymentControllerTest extends AbstractPaymentControllerTest
{
    /**
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }
}

<?php

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsCommonTest\Controller\Application\PaymentSubmission;

use CommonTest\Controller\Application\PaymentSubmission\SummaryControllerTest as AbstractSummaryControllerTest;

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryControllerTest extends AbstractSummaryControllerTest
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

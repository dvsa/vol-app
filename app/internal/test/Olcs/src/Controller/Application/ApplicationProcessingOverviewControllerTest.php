<?php

/**
 * Application Processing controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Application\Processing;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Application Processing controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingOverviewControllerTest extends AbstractHttpControllerTestCase
{

    public function testIndexAction()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Application\Processing\ApplicationProcessingOverviewController',
            ['redirectToRoute']
        );

        $expectedRoute = 'lva-application/processing/notes';

        // assert index action redirects to tasks (for now)
        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with($expectedRoute, [], [], true);

        $this->controller->indexAction();
    }
}

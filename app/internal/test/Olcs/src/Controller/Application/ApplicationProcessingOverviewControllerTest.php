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
        $this->markTestSkipped('Logger service not found to be fixed');

        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            '\Olcs\Controller\Application\Processing\ApplicationProcessingOverviewController',
            ['redirectToRoute']
        );

        $expectedRoute = 'lva-application/processing/tasks';

        // assert index action redirects to tasks (for now)
        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with($expectedRoute, [], ['query' => []], true);

        $this->controller->indexAction();
    }
}

<?php

/**
 * Licence Processing controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Licence\Processing;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Licence Processing controller tests
 *
 * If we add more to this controller it may be worth abstracting to reduce
 * copypasta with ApplicationProcessingOverviewControllerTest
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceProcessingOverviewControllerTest extends AbstractHttpControllerTestCase
{
    public function testIndexAction()
    {
        $this->markTestSkipped('Logger service not found to be fixed');

        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Licence\Processing\LicenceProcessingOverviewController',
            ['redirectToRoute']
        );

        $expectedRoute = 'licence/processing/tasks';

        // assert index action redirects to tasks
        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with($expectedRoute, [], ['query' => []], true);

        $this->controller->indexAction();
    }
}

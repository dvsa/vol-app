<?php

/**
 * Continuation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace AdminTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\Controller\ContinuationController;
use OlcsTest\Bootstrap;

/**
 * Continuation Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ContinuationControllerTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new ContinuationController();
        $this->sm = Bootstrap::getServiceManager();
    }

    public function testIndexAction()
    {
        $this->assertTrue(true);
    }
}

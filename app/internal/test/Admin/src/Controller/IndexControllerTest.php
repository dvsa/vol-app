<?php

/**
 * Index Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace AdminTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\Controller\IndexController;
use OlcsTest\Bootstrap;

/**
 * Index Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IndexControllerTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new IndexController();
        $this->sm = Bootstrap::getServiceManager();
    }

    public function testIndexAction()
    {
        $this->assertTrue(true);
    }
}

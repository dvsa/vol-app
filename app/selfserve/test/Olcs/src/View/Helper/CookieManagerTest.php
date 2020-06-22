<?php

namespace OlcsTest\View\Helper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\View\Helper\CookieManager;

class CookieManagerTest extends MockeryTestCase
{
    /**
     * @var CookieManager
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CookieManager();
    }

    public function testInvoke()
    {
        /** @var \Zend\ServiceManager\ServiceManager | m\MockInterface $mockSl */
        $mockSl = m::mock(\Zend\ServiceManager\ServiceManager::class);
        $mockSl->shouldReceive('getServiceLocator->get')->once()->with('Config')->andReturn(['cookie-manager' => 'TEST']);
        $this->sut->setServiceLocator($mockSl);
        $this->assertEquals('"TEST"', $this->sut->__invoke());
    }
}

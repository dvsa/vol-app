<?php

declare(strict_types=1);

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

    /** @var array */
    protected $config;

    public function setUp(): void
    {
        $this->config = ['cookie-manager' => 'TEST'];
        $this->sut = new CookieManager($this->config);
    }

    public function testInvoke(): void
    {
        $this->assertEquals('"TEST"', $this->sut->__invoke());
    }
}

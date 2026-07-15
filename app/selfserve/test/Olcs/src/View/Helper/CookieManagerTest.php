<?php

declare(strict_types=1);

namespace OlcsTest\View\Helper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\View\Helper\CookieManager;

final class CookieManagerTest extends MockeryTestCase
{
    /**
     * @var CookieManager
     */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $config = ['cookie-manager' => 'TEST'];
        $this->sut = new CookieManager($config);
    }

    public function testInvoke(): void
    {
        $this->assertEquals('"TEST"', $this->sut->__invoke());
    }
}

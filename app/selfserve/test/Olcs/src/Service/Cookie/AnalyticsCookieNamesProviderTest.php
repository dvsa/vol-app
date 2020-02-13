<?php

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\AnalyticsCookieNamesProvider;
use Zend\Http\Header\Cookie;

class AnalyticsCookieNamesProviderTest extends MockeryTestCase
{
    const HOSTNAME = 'host.name';
    const AUGMENTED_HOSTNAME = '.host.name';

    const GID_KEY = '_gid';
    const GID_VALUE = 'abcd1234';

    const GAT_KEY = '_gat';
    const GAT_VALUE = 'mnop2345';

    const GA_KEY = '_ga';
    const GA_VALUE = 'wxyz6666';

    const GAT_1_KEY = '_gat_xyz';
    const GAT_1_VALUE = 'mnbv0000';
    const GAT_2_KEY = '_gat_boo';
    const GAT_2_VALUE = 'aaaabbbb';

    public function testGetNames()
    {
        $cookieContents = [
            self::GID_KEY => 'abcd1234',
            self::GAT_KEY => 'mnop2345',
            'langPref' => 'en_GB',
            self::GA_KEY => 'wxyz6666',
            self::GAT_1_KEY => 'mnvb0000',
            self::GAT_2_KEY => 'aaaabbb',
            'foo' => 'bar',
        ];

        $cookie = m::mock(Cookie::class);
        $cookie->shouldReceive('getArrayCopy')
            ->andReturn($cookieContents);

        $expected = [
            [
                'name' => self::GID_KEY,
                'domain' => self::AUGMENTED_HOSTNAME,
            ],
            [
                'name' => self::GAT_KEY,
                'domain' => self::AUGMENTED_HOSTNAME,
            ],
            [
                'name' => self::GA_KEY,
                'domain' => self::AUGMENTED_HOSTNAME,
            ],
            [
                'name' => self::GAT_1_KEY,
                'domain' => self::AUGMENTED_HOSTNAME,
            ],
            [
                'name' => self::GAT_2_KEY,
                'domain' => self::AUGMENTED_HOSTNAME,
            ],
        ];

        $sut = new AnalyticsCookieNamesProvider(self::HOSTNAME);

        $this->assertEquals(
            $expected,
            $sut->getNames($cookie)
        );
    }
}

<?php

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\AnalyticsCookieNamesProvider;
use Zend\Http\Header\Cookie;

class AnalyticsCookieNamesProviderTest extends MockeryTestCase
{
    const HOSTNAME = 'host.name';

    const GID_KEY = '_gid';
    const GAT_KEY = '_gat';
    const GA_KEY = '_ga';
    const GAT_1_KEY = '_gat_xyz';
    const GAT_2_KEY = '_gat_boo';

    private $cookieContents = [
        self::GID_KEY => 'abcd1234',
        self::GAT_KEY => 'mnop2345',
        'langPref' => 'en_GB',
        self::GA_KEY => 'wxyz6666',
        self::GAT_1_KEY => 'mnvb0000',
        self::GAT_2_KEY => 'aaaabbb',
        'foo' => 'bar',
    ];

    public function setUp(): void
    {
        $this->cookie = m::mock(Cookie::class);
        $this->cookie->shouldReceive('getArrayCopy')
            ->andReturn($this->cookieContents);
    }

    public function testGetNamesForNonProd()
    {
        $domain = 'host.name';

        $expected = [
            [
                'name' => self::GID_KEY,
                'domain' => $domain,
            ],
            [
                'name' => self::GAT_KEY,
                'domain' => $domain,
            ],
            [
                'name' => self::GA_KEY,
                'domain' => $domain,
            ],
            [
                'name' => self::GAT_1_KEY,
                'domain' => $domain,
            ],
            [
                'name' => self::GAT_2_KEY,
                'domain' => $domain,
            ],
        ];

        $sut = new AnalyticsCookieNamesProvider('host.name');

        $this->assertEquals(
            $expected,
            $sut->getNames($this->cookie)
        );
    }

    /**
     * @dataProvider dpGetNamesForProd
     */
    public function testGetNamesForProd($domain)
    {
        $expected = [
            [
                'name' => self::GID_KEY,
                'domain' => $domain,
            ],
            [
                'name' => self::GID_KEY,
                'domain' => AnalyticsCookieNamesProvider::LEGACY_COOKIE_DOMAIN,
            ],
            [
                'name' => self::GAT_KEY,
                'domain' => $domain,
            ],
            [
                'name' => self::GAT_KEY,
                'domain' => AnalyticsCookieNamesProvider::LEGACY_COOKIE_DOMAIN,
            ],
            [
                'name' => self::GA_KEY,
                'domain' => $domain,
            ],
            [
                'name' => self::GA_KEY,
                'domain' => AnalyticsCookieNamesProvider::LEGACY_COOKIE_DOMAIN,
            ],
            [
                'name' => self::GAT_1_KEY,
                'domain' => $domain,
            ],
            [
                'name' => self::GAT_1_KEY,
                'domain' => AnalyticsCookieNamesProvider::LEGACY_COOKIE_DOMAIN,
            ],
            [
                'name' => self::GAT_2_KEY,
                'domain' => $domain,
            ],
            [
                'name' => self::GAT_2_KEY,
                'domain' => AnalyticsCookieNamesProvider::LEGACY_COOKIE_DOMAIN,
            ],
        ];

        $sut = new AnalyticsCookieNamesProvider($domain);

        $this->assertEquals(
            $expected,
            $sut->getNames($this->cookie)
        );
    }

    public function dpGetNamesForProd()
    {
        return [
            ['.www.preview.vehicle-operator-licensing.service.gov.uk'],
            ['.www.vehicle-operator-licensing.service.gov.uk'],
        ];
    }
}

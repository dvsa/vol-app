<?php

/**
 * Url Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Helper;

use Common\Service\Helper\UrlHelperService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\View\HelperPluginManager;
use Laminas\View\Helper\Url;

/**
 * Url Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UrlHelperServiceTest extends MockeryTestCase
{
    /**
     * Holds the SUT
     *
     * @var \Common\Service\Helper\UrlHelperService
     */
    private $sut;

    private $mockViewHelperManager;

    private $mockUrlViewHelper;

    /**
     * Setup the sut
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->mockUrlViewHelper = $this->createPartialMock(Url::class, ['__invoke']);

        $this->mockViewHelperManager = $this->createPartialMock(HelperPluginManager::class, ['get']);
        $this->mockViewHelperManager->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->mockUrlViewHelper));

        $this->sut = new UrlHelperService($this->mockViewHelperManager, []);
    }

    /**
     * @group helper_service
     * @group url_helper_service
     */
    public function testFromRoute(): void
    {
        $route = 'foo/bar';
        $params = ['foo' => 'bar'];
        $options = ['this' => 'that'];
        $reuseMatchedParams = true;
        $builtUrl = 'some/url';

        $this->mockUrlViewHelper->expects($this->once())
            ->method('__invoke')
            ->with($route, $params, $options, $reuseMatchedParams)
            ->will($this->returnValue($builtUrl));

        $this->assertEquals($builtUrl, $this->sut->fromRoute($route, $params, $options, $reuseMatchedParams));
    }

    /**
     * @group helper_service
     * @group url_helper_service
     */
    public function testFromRouteWithDefaults(): void
    {
        $route = 'foo/bar';
        $builtUrl = 'some/url';

        $this->mockUrlViewHelper->expects($this->once())
            ->method('__invoke')
            ->with($route, [], [], false)
            ->will($this->returnValue($builtUrl));

        $this->assertEquals($builtUrl, $this->sut->fromRoute($route));
    }

    public function testFromRouteWithHostWithNoMatchingKey(): void
    {
        $config = [
            'hostnames' => []
        ];

        $this->sut = new UrlHelperService($this->mockViewHelperManager, $config);

        try {
            $this->sut->fromRouteWithHost('foo');
        } catch (\RuntimeException $runtimeException) {
            $this->assertEquals("Hostname for 'foo' not found", $runtimeException->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    public function testFromRouteWithHostWithAndMatchingKey(): void
    {
        $urlMock = function ($route, $params, $options): string {
            $this->assertEquals('a_route', $route);
            $this->assertEquals(
                [
                    'use_canonical' => false
                ],
                $options
            );

            return '/a/url';
        };

        $this->mockUrlViewHelper->expects($this->once())
            ->method('__invoke')
            ->will($this->returnCallback($urlMock));

        $config = [
            'hostnames' => ['foo' => 'http://selfserve']
        ];

        $this->sut = new UrlHelperService($this->mockViewHelperManager, $config);

        $this->assertEquals(
            'http://selfserve/a/url',
            $this->sut->fromRouteWithHost('foo', 'a_route')
        );
    }
}

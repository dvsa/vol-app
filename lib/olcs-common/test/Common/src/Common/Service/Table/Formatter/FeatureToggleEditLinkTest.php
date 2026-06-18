<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\FeatureToggleEditLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class FeatureToggleEditLinkTest
 *
 * @package CommonTest\Service\Table\Formatter
 */
class FeatureToggleEditLinkTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new FeatureToggleEditLink($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormat(): void
    {
        $id = 123;
        $friendlyName = 'friendly name';
        $url = 'http://url.com';

        $data = [
            'id' => $id,
            'friendlyName' => $friendlyName
        ];

        $this->urlHelper->shouldReceive('fromRoute')
            ->once()
            ->with(FeatureToggleEditLink::URL_ROUTE, ['id' => $id, 'action' => FeatureToggleEditLink::URL_ACTION])
            ->andReturn($url);

        $expected = sprintf(FeatureToggleEditLink::LINK_PATTERN, $url, $friendlyName);

        $this->assertEquals($expected, $this->sut->format($data, []));
    }
}

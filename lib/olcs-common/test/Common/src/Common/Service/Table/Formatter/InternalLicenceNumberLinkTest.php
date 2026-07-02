<?php

/**
 * LicenceNumberLinkTest.php
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\InternalLicenceNumberLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class LicenceNumberLinkTest
 *
 * @package CommonTest\Service\Table\Formatter
 */
class InternalLicenceNumberLinkTest extends TestCase
{
    protected $urlHelper;

    protected $translator;

    protected $viewHelperManager;

    protected $router;

    protected $request;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new InternalLicenceNumberLink($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormat(): void
    {
        $licence = [
            'licence' => [
                'id' => 1,
                'licNo' => 0001,
            ]
        ];

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with(
                'lva-licence',
                [
                    'licence' => $licence['licence']['id']
                ]
            )
            ->andReturn('LICENCE_URL');
        $expected = '<a class="govuk-link" href="LICENCE_URL" title="Licence details for 1">1</a>';
        $this->assertEquals($expected, $this->sut->format($licence, []));
    }
}

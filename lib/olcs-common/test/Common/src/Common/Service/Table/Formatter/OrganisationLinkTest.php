<?php

/**
 * OrganisationLinkTest.php
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\OrganisationLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class OrganisationLinkTest
 *
 * @package CommonTest\Service\Table\Formatter
 */
class OrganisationLinkTest extends TestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new OrganisationLink($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormat(): void
    {
        $data = [
            'organisation' => [
                'id' => 69,
                'name' => 'Foobar Ltd.'
            ],
        ];

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with(
                'operator/business-details',
                [
                    'organisation' => $data['organisation']['id'],
                ]
            )
            ->andReturn('ORGANISATION_URL');

        $this->assertEquals(
            '<a class="govuk-link" href="ORGANISATION_URL">Foobar Ltd.</a>',
            $this->sut->format($data, [])
        );
    }
}

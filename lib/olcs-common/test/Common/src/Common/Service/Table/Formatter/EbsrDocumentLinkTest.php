<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\EbsrDocumentLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see EbsrDocumentLink
 */
class EbsrDocumentLinkTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new EbsrDocumentLink($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Tests format
     *
     * @param string $ebsrStatus
     * @param string $colour
     * @param string $label
     */
    public function testFormat(): void
    {
        $submissionId = 123;
        $documentDescription = 'description';
        $url = 'http://url.com';

        $this->urlHelper->shouldReceive('fromRoute')
            ->once()
            ->with(EbsrDocumentLink::URL_ROUTE, ['id' => $submissionId, 'action' => EbsrDocumentLink::URL_ACTION])
            ->andReturn($url);

        $data = [
            'document' => [
                'description' => $documentDescription
            ],
            'id' => $submissionId
        ];

        $expected = sprintf(EbsrDocumentLink::LINK_PATTERN, $url, $documentDescription);

        $this->assertEquals($expected, $this->sut->format($data, []));
    }
}

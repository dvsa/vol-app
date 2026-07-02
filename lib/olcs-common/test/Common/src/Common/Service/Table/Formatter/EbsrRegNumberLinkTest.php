<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\EbsrRegNumberLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Service\Table\Formatter\EbsrRegNumberLink
 */
class EbsrRegNumberLinkTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new EbsrRegNumberLink($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Tests empty string returned if there's no variation number set
     */
    public function testFormatWithNoId(): void
    {
        $this->assertEquals('', $this->sut->format([]));
    }

    /**
     * Tests the formatting for the different possible input array formats
     *
     * @dataProvider formatProvider
     *
     * @param $data
     */
    public function testFormat($data): void
    {
        $id = 1234;
        $regNo = 5678;
        $url = 'the url';

        $this->urlHelper->expects('fromRoute')
            ->with(EbsrRegNumberLink::URL_ROUTE, ['busRegId' => $id])
            ->andReturn($url);

        $expected = sprintf(EbsrRegNumberLink::LINK_PATTERN, $url, $regNo);

        $this->assertEquals($expected, $this->sut->format($data, []));
    }

    /**
     * Data provider for testFormat
     *
     * @return array
     */
    public function formatProvider()
    {
        $id = 1234;
        $regNo = 5678;

        $txcInboxFormat = [
            'id' => $id,
            'regNo' => $regNo,
        ];

        $ebsrSubmissionFormat = [
            'busReg' => $txcInboxFormat
        ];

        return [
            [$txcInboxFormat],
            [$ebsrSubmissionFormat],
        ];
    }
}

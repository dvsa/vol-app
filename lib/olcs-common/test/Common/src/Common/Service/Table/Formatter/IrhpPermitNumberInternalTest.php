<?php

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService as UrlHelper;
use Common\Service\Table\Formatter\IrhpPermitNumberInternal;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IrhpPermitNumberInternal test
 */
class IrhpPermitNumberInternalTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelper::class);
        $this->sut = new IrhpPermitNumberInternal($this->urlHelper);
    }

    public function testFormat(): void
    {
        $licenceId = 200;
        $irhpPermitTypeId = RefData::ECMT_PERMIT_TYPE_ID;

        $row = [
            'permitNumber' => '4>',
            'irhpPermitApplication' => [
                'relatedApplication' => [
                    'licence' => [
                        'id' => $licenceId,
                    ],
                ],
            ],
            'irhpPermitRange' => [
                'irhpPermitStock' => [
                    'irhpPermitType' => [
                        'id' => $irhpPermitTypeId,
                    ],
                ],
            ],
        ];

        $expectedParams = [
            'licence' => $licenceId
        ];
        $expectedOptions = [
            'query' => ['irhpPermitType' => $irhpPermitTypeId]
        ];
        $expectedOutput = '<a class="govuk-link" href="INTERNAL_IRHP_URL">4&gt;</a>'; //escaped as proved by &gt;

        $this->urlHelper->shouldReceive('fromRoute')
            ->with('licence/irhp-permits/permit', $expectedParams, $expectedOptions)
            ->once()
            ->andReturn('INTERNAL_IRHP_URL');

        $this->assertEquals(
            $expectedOutput,
            $this->sut->format($row, null)
        );
    }
}

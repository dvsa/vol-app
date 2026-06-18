<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\CaseEntityNrStatus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Service\Table\Formatter\CaseEntityNrStatus
 */
class CaseEntityNrStatusTest extends MockeryTestCase
{
    public $sut;
    /** @var  \Common\Service\Helper\UrlHelperService | m\MockInterface */
    private $mockUrlHlp;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockUrlHlp = m::mock(\Common\Service\Helper\UrlHelperService::class);
        $this->sut = new CaseEntityNrStatus($this->mockUrlHlp);
    }

    public function testFormatTm(): void
    {
        $tmId = 9999;

        $this->mockUrlHlp
            ->shouldReceive('fromRoute')
            ->with('transport-manager', ['transportManager' => $tmId])
            ->once()
            ->andReturn('EXPECT_URL');

        $data = [
            'caseType' => [
                'id' => \Common\RefData::CASE_TYPE_TM,
            ],
            'transportManager' => [
                'id' => $tmId,
            ],
        ];

        static::assertSame(
            '<a class="govuk-link" href="EXPECT_URL">' . $tmId . '</a>',
            $this->sut->format($data, null)
        );
    }

    public function testFormatLic(): void
    {
        $licId = 9999;

        $this->mockUrlHlp
            ->shouldReceive('fromRoute')
            ->with('lva-licence', ['licence' => $licId])
            ->once()
            ->andReturn('EXPECT_LIC_URL');

        $data = [
            'caseType' => [
                'id' => \Common\RefData::CASE_TYPE_LICENCE,
            ],
            'licence' => [
                'id' => $licId,
                'status' => [
                    'description' => 'unit_LicStatus',
                ],
                'licNo' => 'unit_LicNo',
            ],
        ];

        static::assertSame(
            '<a class="govuk-link" href="EXPECT_LIC_URL">unit_LicNo</a> (unit_LicStatus)',
            $this->sut->format($data, null)
        );
    }

    public function testFormatApp(): void
    {
        $licId = 9999;
        $appId = 8888;

        $this->mockUrlHlp
            ->shouldReceive('fromRoute')
            ->with('lva-licence', ['licence' => $licId])
            ->once()
            ->andReturn('EXPECT_LIC_URL')
            ->shouldReceive('fromRoute')
            ->with('lva-application', ['application' => $appId])
            ->once()
            ->andReturn('EXPECT_APP_URL');

        $data = [
            'caseType' => [
                'id' => \Common\RefData::CASE_TYPE_APPLICATION,
            ],
            'licence' => [
                'id' => $licId,
                'status' => [
                    'description' => 'unit_LicStatus',
                ],
                'licNo' => 'unit_LicNo',
            ],
            'application' => [
                'id' => $appId,
                'status' => [
                    'description' => 'unit_AppStatus',
                ],
            ],
        ];

        static::assertSame(
            '<a class="govuk-link" href="EXPECT_LIC_URL">unit_LicNo</a> (unit_LicStatus)' .
            '<br />/<a class="govuk-link" href="EXPECT_APP_URL">' . $appId . '</a> (unit_AppStatus)',
            $this->sut->format($data, null)
        );
    }
}

<?php

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService as UrlHelper;
use Common\Service\Table\Formatter\IssuedPermitLicencePermitReference;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IssuedPermitLicencePermitReference test
 */
class IssuedPermitLicencePermitReferenceTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelper::class);
        $this->sut = new IssuedPermitLicencePermitReference($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @dataProvider dpFormatLinkToIssuedPermits
     */
    public function testFormatLinkToIssuedPermits($row, $expectedOutput): void
    {
        $this->urlHelper->shouldReceive('fromRoute')
            ->with('licence/irhp-application/irhp-permits', ['irhpAppId' => $row['id'], 'licence' => $row['licenceId'], 'permitTypeId' => $row['typeId']])
            ->andReturn('http://internal/licence/' . $row['licenceId'] . '/irhp-application/' . $row['id'] . '/' . $row['typeId'] . '/irhp-permits/');

        $this->assertEquals(
            $expectedOutput,
            $this->sut->format($row, null)
        );
    }

    /**
     * @return ((int|string)[]|string)[][]
     *
     * @psalm-return list{list{array{id: 3, licenceId: 200, applicationRef: 'ECMT>1234567', typeId: 1}, '<a class="govuk-link" href="http://internal/licence/200/irhp-application/3/1/irhp-permits/">ECMT&gt;1234567</a>'}, list{array{id: 5, licenceId: 202, applicationRef: 'ECMT>2345678', typeId: 2}, '<a class="govuk-link" href="http://internal/licence/202/irhp-application/5/2/irhp-permits/">ECMT&gt;2345678</a>'}, list{array{id: 7, licenceId: 204, applicationRef: 'ECMT>3456789', typeId: 3}, '<a class="govuk-link" href="http://internal/licence/204/irhp-application/7/3/irhp-permits/">ECMT&gt;3456789</a>'}, list{array{id: 44, licenceId: 206, applicationRef: 'IRHP>7654321', typeId: 4}, '<a class="govuk-link" href="http://internal/licence/206/irhp-application/44/4/irhp-permits/">IRHP&gt;7654321</a>'}, list{array{id: 46, licenceId: 208, applicationRef: 'IRHP>6543210', typeId: 5}, '<a class="govuk-link" href="http://internal/licence/208/irhp-application/46/5/irhp-permits/">IRHP&gt;6543210</a>'}}
     */
    public function dpFormatLinkToIssuedPermits(): array
    {
        return [
            [
                [
                    'id' => 3,
                    'licenceId' => 200,
                    'applicationRef' => 'ECMT>1234567',
                    'typeId' => RefData::ECMT_PERMIT_TYPE_ID
                ],
                '<a class="govuk-link" href="http://internal/licence/200/irhp-application/3/1/irhp-permits/">ECMT&gt;1234567</a>'
            ],
            [
                [
                    'id' => 5,
                    'licenceId' => 202,
                    'applicationRef' => 'ECMT>2345678',
                    'typeId' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID
                ],
                '<a class="govuk-link" href="http://internal/licence/202/irhp-application/5/2/irhp-permits/">ECMT&gt;2345678</a>'
            ],
            [
                [
                    'id' => 7,
                    'licenceId' => 204,
                    'applicationRef' => 'ECMT>3456789',
                    'typeId' => RefData::ECMT_REMOVAL_PERMIT_TYPE_ID
                ],
                '<a class="govuk-link" href="http://internal/licence/204/irhp-application/7/3/irhp-permits/">ECMT&gt;3456789</a>'
            ],
            [
                [
                    'id' => 44,
                    'licenceId' => 206,
                    'applicationRef' => 'IRHP>7654321',
                    'typeId' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID
                ],
                '<a class="govuk-link" href="http://internal/licence/206/irhp-application/44/4/irhp-permits/">IRHP&gt;7654321</a>'
            ],
            [
                [
                    'id' => 46,
                    'licenceId' => 208,
                    'applicationRef' => 'IRHP>6543210',
                    'typeId' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID
                ],
                '<a class="govuk-link" href="http://internal/licence/208/irhp-application/46/5/irhp-permits/">IRHP&gt;6543210</a>'
            ]
        ];
    }

    /**
     * @dataProvider dpFormatLinkToApplication
     */
    public function testFormatLinkToApplication($row, $expectedOutput): void
    {
        $this->urlHelper->shouldReceive('fromRoute')
            ->with('licence/irhp-application/application', ['licence' => $row['licenceId'], 'action' => 'edit', 'irhpAppId' => $row['id']])
            ->andReturn('http://internal/licence/' . $row['licenceId'] . '/irhp-application/edit/' . $row['id'] . '/');

        $this->assertEquals(
            $expectedOutput,
            $this->sut->format($row, null)
        );
    }

    /**
     * @return ((int|string)[]|string)[][]
     *
     * @psalm-return list{list{array{id: 100010, licenceId: 212, applicationRef: 'CERT>7654321', typeId: 6}, '<a class="govuk-link" href="http://internal/licence/212/irhp-application/edit/100010/">CERT&gt;7654321</a>'}, list{array{id: 100012, licenceId: 208, applicationRef: 'CERT>6543210', typeId: 7}, '<a class="govuk-link" href="http://internal/licence/208/irhp-application/edit/100012/">CERT&gt;6543210</a>'}}
     */
    public function dpFormatLinkToApplication(): array
    {
        return [
            [
                [
                    'id' => 100010,
                    'licenceId' => 212,
                    'applicationRef' => 'CERT>7654321',
                    'typeId' => RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID
                ],
                '<a class="govuk-link" href="http://internal/licence/212/irhp-application/edit/100010/">CERT&gt;7654321</a>'
            ],
            [
                [
                    'id' => 100012,
                    'licenceId' => 208,
                    'applicationRef' => 'CERT>6543210',
                    'typeId' => RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID
                ],
                '<a class="govuk-link" href="http://internal/licence/208/irhp-application/edit/100012/">CERT&gt;6543210</a>'
            ]
        ];
    }
}

<?php

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Helper\UrlHelperService as UrlHelper;
use Common\Service\Table\Formatter\LicencePermitReference;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Licence permit reference test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class LicencePermitReferenceTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $translator;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelper::class);
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->sut = new LicencePermitReference($this->translator, $this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * @dataProvider scenariosProvider
     */
    public function testFormat($row, $expectedOutput): void
    {
        $this->urlHelper->shouldReceive('fromRoute')
            ->with('permits/application', ['id' => 100])
            ->andReturn('http://selfserve/permits/application/100')
            ->shouldReceive('fromRoute')
            ->with('permits/application/under-consideration', ['id' => 101])
            ->andReturn('http://selfserve/permits/application/101/under-consideration')
            ->shouldReceive('fromRoute')
            ->with('permits/application/awaiting-fee', ['id' => 102])
            ->andReturn('http://selfserve/permits/application/102/awaiting-fee')
            ->shouldReceive('fromRoute')
            ->with('permits/valid', ['licence' => 200, 'type' => $row['typeId']])
            ->andReturn('http://selfserve/permits/valid/105');

        $this->translator->shouldReceive('translate')
            ->with('dashboard-table-permit-application-ref')
            ->andReturn('Reference number');

        $this->assertEquals(
            $expectedOutput,
            $this->sut->format($row, null)
        );
    }

    /**
     * @return ((int|string)[]|string)[][]
     *
     * @psalm-return array{'ECMT Annual - not yet submitted': list{array{id: 100, licenceId: 200, licNo: 'ECMT>', applicationRef: 'ECMT>1234567', typeId: 1, statusId: 'permit_app_nys'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100"><span class="overview__link--underline">ECMT&gt;1234567</span></a>'}, 'ECMT Annual - under consideration': list{array{id: 101, licenceId: 200, licNo: 'ECMT>', applicationRef: 'ECMT>2345678', typeId: 1, statusId: 'permit_app_uc'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/101/under-consideration"><span class="overview__link--underline">ECMT&gt;2345678</span></a>'}, 'ECMT Annual - awaiting fee': list{array{id: 102, licenceId: 200, licNo: 'ECMT>', applicationRef: 'ECMT>3456789', typeId: 1, statusId: 'permit_app_awaiting'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/102/awaiting-fee"><span class="overview__link--underline">ECMT&gt;3456789</span></a>'}, 'ECMT Annual - fee paid': list{array{id: 8, licenceId: 200, licNo: 'ECMT>', applicationRef: 'ECMT>3456789', typeId: 1, statusId: 'permit_app_fee_paid'}, '<span class="govuk-visually-hidden">Reference number</span> ECMT&gt;3456789'}, 'ECMT Annual - issuing': list{array{id: 8, licenceId: 200, licNo: 'ECMT>', applicationRef: 'ECMT>3456789', typeId: 1, statusId: 'permit_app_issuing'}, '<span class="govuk-visually-hidden">Reference number</span> ECMT&gt;3456789'}, 'ECMT Annual - valid': list{array{id: 105, licenceId: 200, licNo: 'ECMT>', applicationRef: 'ECMT>4567890', typeId: 1, statusId: 'permit_app_valid'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/valid/105"><span class="overview__link--underline">ECMT&gt;</span></a>'}, 'ECMT Short Term app - not yet submitted': list{array{id: 100, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC100', typeId: 2, statusId: 'permit_app_nys'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100"><span class="overview__link--underline">IRHP&gt;ABC100</span></a>'}, 'ECMT Short Term app - under consideration': list{array{id: 101, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC101', typeId: 2, statusId: 'permit_app_uc'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/101/under-consideration"><span class="overview__link--underline">IRHP&gt;ABC101</span></a>'}, 'ECMT Short Term app - awaiting fee': list{array{id: 102, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC102', typeId: 2, statusId: 'permit_app_awaiting'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/102/awaiting-fee"><span class="overview__link--underline">IRHP&gt;ABC102</span></a>'}, 'ECMT Short Term app - fee paid': list{array{id: 103, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC103', typeId: 2, statusId: 'permit_app_fee_paid'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC103'}, 'ECMT Short Term app - issuing': list{array{id: 104, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC104', typeId: 2, statusId: 'permit_app_issuing'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC104'}, 'ECMT Short Term app - valid': list{array{id: 105, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC105', typeId: 2, statusId: 'permit_app_valid'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/valid/105"><span class="overview__link--underline">IRHP&gt;</span></a>'}, 'IRHP Bilateral app - not yet submitted': list{array{id: 100, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC100', typeId: 4, statusId: 'permit_app_nys'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100"><span class="overview__link--underline">IRHP&gt;ABC100</span></a>'}, 'IRHP Bilateral app - under consideration': list{array{id: 101, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC101', typeId: 4, statusId: 'permit_app_uc'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC101'}, 'IRHP Bilateral app - awaiting fee': list{array{id: 102, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC102', typeId: 4, statusId: 'permit_app_awaiting'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC102'}, 'IRHP Bilateral app - fee paid': list{array{id: 103, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC103', typeId: 4, statusId: 'permit_app_fee_paid'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC103'}, 'IRHP Bilateral app - issuing': list{array{id: 104, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC104', typeId: 4, statusId: 'permit_app_issuing'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC104'}, 'IRHP Bilateral app - valid': list{array{id: 105, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC105', typeId: 4, statusId: 'permit_app_valid'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/valid/105"><span class="overview__link--underline">IRHP&gt;</span></a>'}, 'IRHP Multilateral app - not yet submitted': list{array{id: 100, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC100', typeId: 5, statusId: 'permit_app_nys'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100"><span class="overview__link--underline">IRHP&gt;ABC100</span></a>'}, 'IRHP Multilateral app - under consideration': list{array{id: 101, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC101', typeId: 5, statusId: 'permit_app_uc'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC101'}, 'IRHP Multilateral app - awaiting fee': list{array{id: 102, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC102', typeId: 5, statusId: 'permit_app_awaiting'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC102'}, 'IRHP Multilateral app - fee paid': list{array{id: 103, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC103', typeId: 5, statusId: 'permit_app_fee_paid'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC103'}, 'IRHP Multilateral app - issuing': list{array{id: 104, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC104', typeId: 5, statusId: 'permit_app_issuing'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC104'}, 'IRHP Multilateral app - valid': list{array{id: 105, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC105', typeId: 5, statusId: 'permit_app_valid'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/valid/105"><span class="overview__link--underline">IRHP&gt;</span></a>'}, 'IRHP Ecmt removal - not yet submitted': list{array{id: 100, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC100', typeId: 3, statusId: 'permit_app_nys'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100"><span class="overview__link--underline">IRHP&gt;ABC100</span></a>'}, 'IRHP Ecmt removal - under consideration': list{array{id: 101, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC101', typeId: 3, statusId: 'permit_app_uc'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC101'}, 'IRHP Ecmt removal - awaiting fee': list{array{id: 102, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC102', typeId: 3, statusId: 'permit_app_awaiting'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC102'}, 'IRHP Ecmt removal - fee paid': list{array{id: 103, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC103', typeId: 3, statusId: 'permit_app_fee_paid'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC103'}, 'IRHP Ecmt removal - issuing': list{array{id: 104, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC104', typeId: 3, statusId: 'permit_app_issuing'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC104'}, 'IRHP Ecmt removal - valid': list{array{id: 105, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC105', typeId: 3, statusId: 'permit_app_valid'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/valid/105"><span class="overview__link--underline">IRHP&gt;</span></a>'}, 'Certificate of Roadworthiness for vehicle - not yet submitted': list{array{id: 100, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC100', typeId: 6, statusId: 'permit_app_nys'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100"><span class="overview__link--underline">IRHP&gt;ABC100</span></a>'}, 'Certificate of Roadworthiness for vehicle - under consideration': list{array{id: 101, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC101', typeId: 6, statusId: 'permit_app_uc'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC101'}, 'Certificate of Roadworthiness for vehicle - awaiting fee': list{array{id: 102, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC102', typeId: 6, statusId: 'permit_app_awaiting'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC102'}, 'Certificate of Roadworthiness for vehicle - fee paid': list{array{id: 103, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC103', typeId: 6, statusId: 'permit_app_fee_paid'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC103'}, 'Certificate of Roadworthiness for vehicle - issuing': list{array{id: 104, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC104', typeId: 6, statusId: 'permit_app_issuing'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC104'}, 'Certificate of Roadworthiness for vehicle - valid': list{array{id: 105, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC105', typeId: 6, statusId: 'permit_app_valid'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;'}, 'Certificate of Roadworthiness for trailer - not yet submitted': list{array{id: 100, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC100', typeId: 7, statusId: 'permit_app_nys'}, '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100"><span class="overview__link--underline">IRHP&gt;ABC100</span></a>'}, 'Certificate of Roadworthiness for trailer - under consideration': list{array{id: 101, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC101', typeId: 7, statusId: 'permit_app_uc'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC101'}, 'Certificate of Roadworthiness for trailer - awaiting fee': list{array{id: 102, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC102', typeId: 7, statusId: 'permit_app_awaiting'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC102'}, 'Certificate of Roadworthiness for trailer - fee paid': list{array{id: 103, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC103', typeId: 7, statusId: 'permit_app_fee_paid'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC103'}, 'Certificate of Roadworthiness for trailer - issuing': list{array{id: 104, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC104', typeId: 7, statusId: 'permit_app_issuing'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC104'}, 'Certificate of Roadworthiness for trailer - valid': list{array{id: 105, licenceId: 200, licNo: 'IRHP>', applicationRef: 'IRHP>ABC105', typeId: 7, statusId: 'permit_app_valid'}, '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;'}}
     */
    public function scenariosProvider(): array
    {
        return [
            'ECMT Annual - not yet submitted' => [
                [
                    'id' => 100,
                    'licenceId' => 200,
                    'licNo' => 'ECMT>',
                    'applicationRef' => 'ECMT>1234567',
                    'typeId' => RefData::ECMT_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100">' .
                    '<span class="overview__link--underline">ECMT&gt;1234567</span></a>'
            ],
            'ECMT Annual - under consideration' => [
                [   'id' => 101,
                    'licenceId' => 200,
                    'licNo' => 'ECMT>',
                    'applicationRef' => 'ECMT>2345678',
                    'typeId' => RefData::ECMT_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/101/under-consideration">' .
                    '<span class="overview__link--underline">ECMT&gt;2345678</span></a>'
            ],
            'ECMT Annual - awaiting fee' => [
                [
                    'id' => 102,
                    'licenceId' => 200,
                    'licNo' => 'ECMT>',
                    'applicationRef' => 'ECMT>3456789',
                    'typeId' => RefData::ECMT_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_AWAITING_FEE,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/102/awaiting-fee">' .
                    '<span class="overview__link--underline">ECMT&gt;3456789</span></a>'
            ],
            'ECMT Annual - fee paid' => [
                [
                    'id' => 8,
                    'licenceId' => 200,
                    'licNo' => 'ECMT>',
                    'applicationRef' => 'ECMT>3456789',
                    'typeId' => RefData::ECMT_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_FEE_PAID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> ECMT&gt;3456789'
            ],
            'ECMT Annual - issuing' => [
                [
                    'id' => 8,
                    'licenceId' => 200,
                    'licNo' => 'ECMT>',
                    'applicationRef' => 'ECMT>3456789',
                    'typeId' => RefData::ECMT_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_ISSUING,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> ECMT&gt;3456789'
            ],
            'ECMT Annual - valid' => [
                [
                    'id' => 105,
                    'licenceId' => 200,
                    'licNo' => 'ECMT>',
                    'applicationRef' => 'ECMT>4567890',
                    'typeId' => RefData::ECMT_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_VALID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/valid/105">' .
                    '<span class="overview__link--underline">ECMT&gt;</span></a>'
            ],
            'ECMT Short Term app - not yet submitted' => [
                [
                    'id' => 100,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC100',
                    'typeId' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100">' .
                    '<span class="overview__link--underline">IRHP&gt;ABC100</span></a>'
            ],
            'ECMT Short Term app - under consideration' => [
                [
                    'id' => 101,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC101',
                    'typeId' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/101/under-consideration">' .
                    '<span class="overview__link--underline">IRHP&gt;ABC101</span></a>'
            ],
            'ECMT Short Term app - awaiting fee' => [
                [
                    'id' => 102,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC102',
                    'typeId' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_AWAITING_FEE,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/102/awaiting-fee">' .
                    '<span class="overview__link--underline">IRHP&gt;ABC102</span></a>'
            ],
            'ECMT Short Term app - fee paid' => [
                [
                    'id' => 103,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC103',
                    'typeId' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_FEE_PAID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC103'
            ],
            'ECMT Short Term app - issuing' => [
                [
                    'id' => 104,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC104',
                    'typeId' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_ISSUING,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC104'
            ],
            'ECMT Short Term app - valid' => [
                [
                    'id' => 105,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC105',
                    'typeId' => RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_VALID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/valid/105">' .
                    '<span class="overview__link--underline">IRHP&gt;</span></a>'
            ],
            'IRHP Bilateral app - not yet submitted' => [
                [
                    'id' => 100,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC100',
                    'typeId' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100">' .
                    '<span class="overview__link--underline">IRHP&gt;ABC100</span></a>'
            ],
            'IRHP Bilateral app - under consideration' => [
                [
                    'id' => 101,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC101',
                    'typeId' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC101'
            ],
            'IRHP Bilateral app - awaiting fee' => [
                [
                    'id' => 102,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC102',
                    'typeId' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_AWAITING_FEE,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC102'
            ],
            'IRHP Bilateral app - fee paid' => [
                [
                    'id' => 103,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC103',
                    'typeId' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_FEE_PAID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC103'
            ],
            'IRHP Bilateral app - issuing' => [
                [
                    'id' => 104,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC104',
                    'typeId' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_ISSUING,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC104'
            ],
            'IRHP Bilateral app - valid' => [
                [
                    'id' => 105,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC105',
                    'typeId' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_VALID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/valid/105">' .
                    '<span class="overview__link--underline">IRHP&gt;</span></a>'
            ],
            'IRHP Multilateral app - not yet submitted' => [
                [
                    'id' => 100,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC100',
                    'typeId' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100">' .
                    '<span class="overview__link--underline">IRHP&gt;ABC100</span></a>'
            ],
            'IRHP Multilateral app - under consideration' => [
                [
                    'id' => 101,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC101',
                    'typeId' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC101'
            ],
            'IRHP Multilateral app - awaiting fee' => [
                [
                    'id' => 102,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC102',
                    'typeId' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_AWAITING_FEE,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC102'
            ],
            'IRHP Multilateral app - fee paid' => [
                [
                    'id' => 103,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC103',
                    'typeId' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_FEE_PAID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC103'
            ],
            'IRHP Multilateral app - issuing' => [
                [
                    'id' => 104,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC104',
                    'typeId' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_ISSUING,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC104'
            ],
            'IRHP Multilateral app - valid' => [
                [
                    'id' => 105,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC105',
                    'typeId' => RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_VALID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/valid/105">' .
                    '<span class="overview__link--underline">IRHP&gt;</span></a>'
            ],
            'IRHP Ecmt removal - not yet submitted' => [
                [
                    'id' => 100,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC100',
                    'typeId' => RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100">' .
                '<span class="overview__link--underline">IRHP&gt;ABC100</span></a>'
            ],
            'IRHP Ecmt removal - under consideration' => [
                [
                    'id' => 101,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC101',
                    'typeId' => RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC101'
            ],
            'IRHP Ecmt removal - awaiting fee' => [
                [
                    'id' => 102,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC102',
                    'typeId' => RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_AWAITING_FEE,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC102'
            ],
            'IRHP Ecmt removal - fee paid' => [
                [
                    'id' => 103,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC103',
                    'typeId' => RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_FEE_PAID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC103'
            ],
            'IRHP Ecmt removal - issuing' => [
                [
                    'id' => 104,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC104',
                    'typeId' => RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_ISSUING,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC104'
            ],
            'IRHP Ecmt removal - valid' => [
                [
                    'id' => 105,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC105',
                    'typeId' => RefData::ECMT_REMOVAL_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_VALID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/valid/105">' .
                '<span class="overview__link--underline">IRHP&gt;</span></a>'
            ],
            'Certificate of Roadworthiness for vehicle - not yet submitted' => [
                [
                    'id' => 100,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC100',
                    'typeId' => RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100">' .
                '<span class="overview__link--underline">IRHP&gt;ABC100</span></a>'
            ],
            'Certificate of Roadworthiness for vehicle - under consideration' => [
                [
                    'id' => 101,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC101',
                    'typeId' => RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC101'
            ],
            'Certificate of Roadworthiness for vehicle - awaiting fee' => [
                [
                    'id' => 102,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC102',
                    'typeId' => RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_AWAITING_FEE,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC102'
            ],
            'Certificate of Roadworthiness for vehicle - fee paid' => [
                [
                    'id' => 103,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC103',
                    'typeId' => RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_FEE_PAID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC103'
            ],
            'Certificate of Roadworthiness for vehicle - issuing' => [
                [
                    'id' => 104,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC104',
                    'typeId' => RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_ISSUING,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC104'
            ],
            'Certificate of Roadworthiness for vehicle - valid' => [
                [
                    'id' => 105,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC105',
                    'typeId' => RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_VALID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;'
            ],
            'Certificate of Roadworthiness for trailer - not yet submitted' => [
                [
                    'id' => 100,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC100',
                    'typeId' => RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_NOT_YET_SUBMITTED,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> <a class="overview__link" href="http://selfserve/permits/application/100">' .
                '<span class="overview__link--underline">IRHP&gt;ABC100</span></a>'
            ],
            'Certificate of Roadworthiness for trailer - under consideration' => [
                [
                    'id' => 101,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC101',
                    'typeId' => RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC101'
            ],
            'Certificate of Roadworthiness for trailer - awaiting fee' => [
                [
                    'id' => 102,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC102',
                    'typeId' => RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_AWAITING_FEE,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC102'
            ],
            'Certificate of Roadworthiness for trailer - fee paid' => [
                [
                    'id' => 103,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC103',
                    'typeId' => RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_FEE_PAID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC103'
            ],
            'Certificate of Roadworthiness for trailer - issuing' => [
                [
                    'id' => 104,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC104',
                    'typeId' => RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_ISSUING,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;ABC104'
            ],
            'Certificate of Roadworthiness for trailer - valid' => [
                [
                    'id' => 105,
                    'licenceId' => 200,
                    'licNo' => 'IRHP>',
                    'applicationRef' => 'IRHP>ABC105',
                    'typeId' => RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID,
                    'statusId' => RefData::PERMIT_APP_STATUS_VALID,
                ],
                '<span class="govuk-visually-hidden">Reference number</span> IRHP&gt;'
            ],
        ];
    }
}

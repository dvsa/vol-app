<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Common\View\Helper\CurrencyFormatter;
use Common\RefData;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * @todo clearly this will need to be a lot better later - but will wait to see first if it's staying
 *
 * Fee list mapper
 */
class FeeList
{
    /** @var TranslationHelperService */
    private $translator;

    /** @var CurrencyFormatter */
    private $currencyFormatter;

    /** @var EcmtNoOfPermits */
    private $ecmtNoOfPermits;

    /**
     * Create service instance
     *
     * @param TranslationHelperService $translator
     * @param CurrencyFormatter $currencyFormatter
     * @param EcmtNoOfPermits $ecmtNoOfPermits
     *
     * @return FeeList
     */
    public function __construct(
        TranslationHelperService $translator,
        CurrencyFormatter $currencyFormatter,
        EcmtNoOfPermits $ecmtNoOfPermits
    ) {
        $this->translator = $translator;
        $this->currencyFormatter = $currencyFormatter;
        $this->ecmtNoOfPermits = $ecmtNoOfPermits;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function mapForDisplay(array $data)
    {
        if ($data['application']['permitType']['id'] == RefData::PERMIT_TYPE_ECMT) {
            $additionalSummaryData = $this->getEcmtAnnualSummaryData($data);
        } else {
            $additionalSummaryData = $this->getOtherSummaryData($data);
        }

        $data['application']['summaryData'] = array_merge(
            $this->getBaseSummaryData($data),
            $additionalSummaryData
        );

        return $data['application'];
    }

    /**
     * Get the base summary data common to all permit types
     *
     * @param array $data
     *
     * @return array
     */
    private function getBaseSummaryData($data)
    {
        return [
            [
                'key' => 'permits.page.fee.application.reference',
                'value' => $data['application']['applicationRef']
            ],
            [
                'key' => 'permits.page.fee.application.date',
                'value' => date(\DATE_FORMAT, strtotime($data['application']['dateReceived']))
            ],
            [
                'key' => 'permits.page.fee.permit.type',
                'value' => $data['application']['permitType']['description']
            ],
        ];
    }

    /**
     * Get the additional summary data specific to ecmt annual
     *
     * @param array $data
     *
     * @return array
     */
    private function getEcmtAnnualSummaryData(array $data)
    {
        $currencyFormatter = $this->currencyFormatter;

        $irhpPermitStock = $data['application']['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];

        $totalPermitsRequired = $data['application']['totalPermitsRequired'];
        $applicationFeePerPermit = $data['irhpFeeList']['fee']['IRHP_GV_APP_ECMT']['fixedValue'];
        $permitsRequiredLines = $this->ecmtNoOfPermits->mapForDisplay($data['application']);

        return [
            [
                'key' => 'permits.page.fee.permit.year',
                'value' => date('Y', strtotime($irhpPermitStock['validTo']))
            ],
            [
                'key' => 'permits.page.fee.number.permits',
                'value' => implode('<br/>', $permitsRequiredLines),
                'disableHtmlEscape' => true
            ],
            [
                'key' => 'permits.page.fee.application.fee.per.permit',
                'value' => $applicationFeePerPermit,
                'isCurrency' => true
            ],
            [
                'key' => 'permits.page.fee.permit.fee.total',
                'value' => $this->translator->translateReplace(
                    'permits.page.fee.permit.fee.non-refundable',
                    [$currencyFormatter($applicationFeePerPermit * $totalPermitsRequired)]
                )
            ]
        ];
    }

    /**
     * Get the additional summary data for other permit types
     *
     * @param array $data
     *
     * @return array
     */
    private function getOtherSummaryData(array $data)
    {
        $currencyFormatter = $this->currencyFormatter;

        $irhpPermitStock = $data['application']['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];
        $data['application']['appFee'] = $data['irhpFeeList']['fee']['IRHP_GV_APP_ECMT']['fixedValue'];
        $data['application']['issueFee'] = $data['irhpFeeList']['fee']['IRHP_GV_ECMT_100_PERMIT_FEE']['fixedValue'];

        return [
            [
                'key' => 'permits.page.fee.permit.validity',
                'value' => $this->translator->translateReplace(
                    'permits.page.fee.permit.validity.dates',
                    [
                        date(\DATE_FORMAT, strtotime($irhpPermitStock['validFrom'])),
                        date(\DATE_FORMAT, strtotime($irhpPermitStock['validTo']))
                    ]
                )
            ],
            [
                'key' => 'permits.page.fee.number.permits.required',
                'value' => $this->translator->translateReplace(
                    'permits.page.fee.number.permits.value',
                    [
                        $data['application']['permitsRequired'],
                        $currencyFormatter($data['application']['appFee'])
                    ]
                )
            ],
            [
                'key' => 'permits.page.fee.permit.fee.total',
                'value' => $this->translator->translateReplace(
                    'permits.page.fee.permit.fee.non-refundable',
                    [
                        $currencyFormatter($data['application']['appFee'] * $data['application']['permitsRequired'])
                    ]
                )
            ]
        ];
    }
}

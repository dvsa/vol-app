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
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array $data
     * @param TranslationHelperService $translator
     * @param Url $url
     *
     * @return array
     */
    public static function mapForDisplay(array $data, TranslationHelperService $translator, Url $url)
    {
        if ($data['application']['permitType']['id'] == RefData::PERMIT_TYPE_ECMT) {
            $additionalSummaryData = self::getEcmtAnnualSummaryData($data, $translator, $url);
        } else {
            $additionalSummaryData = self::getOtherSummaryData($data, $translator);
        }

        $data['application']['summaryData'] = array_merge(
            self::getBaseSummaryData($data),
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
    private static function getBaseSummaryData($data)
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
     * @param TranslationHelperService $translator
     * @param Url $url
     *
     * @return array
     */
    private static function getEcmtAnnualSummaryData(array $data, TranslationHelperService $translator, Url $url)
    {
        $currency = new CurrencyFormatter();

        $irhpPermitStock = $data['application']['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];

        $totalPermitsRequired = $data['application']['totalPermitsRequired'];
        $applicationFeePerPermit = $data['irhpFeeList']['fee']['IRHP_GV_APP_ECMT']['fixedValue'];
        $permitsRequiredLines = EcmtNoOfPermits::mapForDisplay($data['application'], $translator, $url);

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
                'value' => $translator->translateReplace(
                    'permits.page.fee.permit.fee.non-refundable',
                    [$currency($applicationFeePerPermit * $totalPermitsRequired)]
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
    private static function getOtherSummaryData(array $data, TranslationHelperService $translator)
    {
        $currency = new CurrencyFormatter;

        $irhpPermitStock = $data['application']['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];
        $data['application']['appFee'] = $data['irhpFeeList']['fee']['IRHP_GV_APP_ECMT']['fixedValue'];
        $data['application']['issueFee'] = $data['irhpFeeList']['fee']['IRHP_GV_ECMT_100_PERMIT_FEE']['fixedValue'];

        return [
            [
                'key' => 'permits.page.fee.permit.validity',
                'value' => $translator->translateReplace(
                    'permits.page.fee.permit.validity.dates',
                    [
                        date(\DATE_FORMAT, strtotime($irhpPermitStock['validFrom'])),
                        date(\DATE_FORMAT, strtotime($irhpPermitStock['validTo']))
                    ]
                )
            ],
            [
                'key' => 'permits.page.fee.number.permits.required',
                'value' => $translator->translateReplace(
                    'permits.page.fee.number.permits.value',
                    [
                        $data['application']['permitsRequired'],
                        $currency($data['application']['appFee'])
                    ]
                )
            ],
            [
                'key' => 'permits.page.fee.permit.fee.total',
                'value' => $translator->translateReplace(
                    'permits.page.fee.permit.fee.non-refundable',
                    [
                        $currency($data['application']['appFee'] * $data['application']['permitsRequired'])
                    ]
                )
            ]
        ];
    }
}

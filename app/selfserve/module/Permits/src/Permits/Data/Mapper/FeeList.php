<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Common\View\Helper\CurrencyFormatter;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * @todo clearly this will need to be a lot better later - but will wait to see first if it's staying
 *
 * Fee list mapper
 */
class FeeList
{
    public static function mapForDisplay(array $data, TranslationHelperService $translator, Url $url)
    {
        $currency = new CurrencyFormatter;

        $irhpPermitStock = $data['application']['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];
        $data['application']['appFee'] = $data['irhpFeeList']['fee']['IRHP_GV_APP_ECMT']['fixedValue'];
        $data['application']['issueFee'] = $data['irhpFeeList']['fee']['IRHP_GV_ECMT_100_PERMIT_FEE']['fixedValue'];

        $summaryData = [
            0 => [
                'key' => 'permits.page.fee.application.reference',
                'value' => $data['application']['applicationRef']
            ],
            1 => [
                'key' => 'permits.page.fee.application.date',
                'value' => date(\DATE_FORMAT, strtotime($data['application']['dateReceived']))
            ],
            2 => [
                'key' => 'permits.page.fee.permit.type',
                'value' => $data['application']['permitType']['description']
            ],
            3 => [
                'key' => 'permits.page.fee.permit.validity',
                'value' => $translator->translateReplace(
                    'permits.page.fee.permit.validity.dates',
                    [
                        date(\DATE_FORMAT, strtotime($irhpPermitStock['validFrom'])),
                        date(\DATE_FORMAT, strtotime($irhpPermitStock['validTo']))
                    ]
                )
            ],
            4 => [
                'key' => 'permits.page.fee.number.permits',
                'value' => $translator->translateReplace(
                    'permits.page.fee.per-permit',
                    [
                        $data['application']['permitsRequired'],
                        $currency($data['application']['appFee'])
                    ]
                )
            ],
            5 => [
                'key' => 'permits.page.fee.permit.fee.total',
                'value' => $translator->translateReplace(
                    'permits.page.fee.permit.fee.non-refundable',
                    [
                        $currency($data['application']['appFee'] * $data['application']['permitsRequired'])
                    ]
                )
            ]
        ];

        $data['application']['summaryData'] = $summaryData;

        return $data['application'];
    }
}

<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Common\View\Helper\CurrencyFormatter;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * Mapper for the IRHP application fee summary page
 */
class IrhpApplicationFeeSummary
{
    const APP_REFERENCE_HEADING = 'permits.page.fee.application.reference';
    const APP_DATE_HEADING = 'permits.page.fee.application.date';
    const PERMIT_TYPE_HEADING = 'permits.page.fee.permit.type';
    const NUM_PERMITS_HEADING = 'permits.page.fee.number.permits';
    const FEE_TOTAL_HEADING = 'permits.page.irhp-fee.permit.fee.total';

    /**
     * Map IRHP application data for use on the fee summary page
     *
     * @param array $data input data
     *
     * @return array
     * @throws \Exception
     */
    public static function mapForDisplay(array $data, TranslationHelperService $translator, Url $url)
    {
        $receivedDate = new \DateTime($data['dateReceived']);
        $currencyFormatter = new CurrencyFormatter();

        $data['mappedFeeData'] = [
            [
                'key' => self::APP_REFERENCE_HEADING,
                'value' => $data['applicationRef'],
            ],
            [
                'key' => self::APP_DATE_HEADING,
                'value' => $receivedDate->format('d F Y'),
            ],
            [
                'key' => self::PERMIT_TYPE_HEADING,
                'value' => $data['irhpPermitType']['name']['description'],
            ],
            [
                'key' => self::NUM_PERMITS_HEADING,
                'value' => $data['permitsRequired'],
            ],
            [
                'key' => self::FEE_TOTAL_HEADING,
                'value' => $translator->translateReplace(
                    'permits.page.fee.permit.fee.non-refundable',
                    [
                        $currencyFormatter($data['outstandingFeeAmount'])
                    ]
                ),
            ],
        ];

        return $data;
    }
}

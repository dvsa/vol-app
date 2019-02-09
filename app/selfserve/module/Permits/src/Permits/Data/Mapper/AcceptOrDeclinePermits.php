<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Common\View\Helper\CurrencyFormatter;
use Permits\View\Helper\EcmtSection;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * Mapper for the FeePartSuccessful / Accept or Decline page
 */
class AcceptOrDeclinePermits
{
    /**
     * Maps data appropriately for the Definition List on the FeePartSuccessful page
     *
     * @param array $data an array of data retrieved from the backend
     * @return array
     */
    public static function mapForDisplay(array $data, TranslationHelperService $translator, Url $url): array
    {
        $currency = new CurrencyFormatter;

        $data = ApplicationFees::mapForDisplay($data, $translator, $url);
        $permitsAwarded = $data['irhpPermitApplications'][0]['permitsAwarded'];
        $stock = $data['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];
        $issueFee = [];
        $summaryData = [];

        if ($data['hasOutstandingFees'] === 0) {
            $data['title'] = 'waived-paid-permits.page.fee-part-successful.title';
            $dueDateKey = 'waived.paid.permits.page.ecmt.fee-part-successful.payment.due';
        } else {
            $data['title'] = 'permits.page.fee-part-successful.title';
            $dueDateKey = 'permits.page.ecmt.fee-part-successful.payment.due';

            $issueFee = [
                'key' => 'permits.page.ecmt.fee-part-successful.issuing.fee',
                'value' => $translator->translateReplace(
                    'permits.page.fee.per-permit',
                    [
                        $permitsAwarded,
                        $currency($data['issueFee']),
                        $url->fromRoute(EcmtSection::ROUTE_ECMT_UNPAID_PERMITS, [], [], true)
                    ]
                ),
                'disableHtmlEscape' => true
            ];
            $issueFeeTotal = [
                'key' => 'permits.page.ecmt.fee-part-successful.issuing.fee.total',
                'value' => $translator->translateReplace(
                    'permits.page.ecmt.fee-part-successful.fee.total.value',
                    [
                        $currency($data['totalFee'])
                    ]
                )
            ];
        }

        $dueDate = [
            'key' => $dueDateKey,
            'value' => date(\DATE_FORMAT, strtotime($data['dueDate']))
        ];

        $summaryData = [
            0 => [
                'key' => 'permits.page.ecmt.consideration.reference.number',
                'value' => $data['applicationRef']
            ],
            1 => [
                'key' => 'permits.page.ecmt.consideration.permit.type',
                'value' => $data['permitType']['description']
            ],
            2 => [
                'key' => 'permits.page.ecmt.fee-part-successful.permit.validity',
                'value' => $translator->translateReplace(
                    'permits.page.fee.permit.validity.dates',
                    [
                        date(\DATE_FORMAT, strtotime($stock['validFrom'])),
                        date(\DATE_FORMAT, strtotime($stock['validTo']))
                    ]
                )
            ]
        ];

        if (isset($issueFee['key'])) {
            array_push($summaryData, $issueFee);
            array_push($summaryData, $issueFeeTotal);
        }

        array_push($summaryData, $dueDate);

        $data['summaryData'] = $summaryData;

        $markup = ($permitsAwarded === $data['permitsRequired']) ? 'markup-ecmt-fee-successful-hint' : 'markup-ecmt-fee-part-successful-hint';

        $data['guidance'] = [
            'value' => $translator->translateReplace(
                $markup,
                [
                    $permitsAwarded,
                    $data['permitsRequired']
                ]
            ),
            'disableHtmlEscape' => true
        ];

        return $data;
    }
}

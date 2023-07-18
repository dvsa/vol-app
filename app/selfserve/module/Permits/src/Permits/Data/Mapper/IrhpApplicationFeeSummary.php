<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\View\Helper\CurrencyFormatter;
use Common\View\Helper\Status as StatusFormatter;
use DateTime;
use RuntimeException;

/**
 * Mapper for the IRHP application fee summary page
 */
class IrhpApplicationFeeSummary implements MapperInterface
{
    use MapFromResultTrait;
    public const APP_REFERENCE_HEADING = 'permits.page.fee.application.reference';
    public const APP_DATE_HEADING = 'permits.page.fee.application.date';
    public const FEE_PER_PERMIT_HEADING = 'permits.irhp.fee-breakdown.fee-per-permit';
    public const APP_FEE_PER_PERMIT_HEADING = 'permits.page.fee.application.fee.per.permit';
    public const ISSUE_FEE_PER_PERMIT_HEADING = 'permits.page.fee.issue.fee.per.permit';
    public const PERMIT_STATUS_HEADING = 'permits.page.fee.permit.status';
    public const PERMIT_TYPE_HEADING = 'permits.page.fee.permit.type';
    public const PERMIT_YEAR_HEADING = 'permits.page.fee.permit.year';
    public const PERMIT_PERIOD_HEADING = 'permits.page.fee.permit.period';
    public const NUM_PERMITS_HEADING = 'permits.page.fee.number.permits';
    public const FEE_TOTAL_HEADING = 'permits.page.irhp-fee.permit.fee.total';
    public const TOTAL_ISSUE_FEE_HEADING = 'permits.page.fee.permit.fee.issue.total';
    public const TOTAL_APPLICATION_FEE_HEADING = 'permits.page.fee.permit.fee.total';
    public const TOTAL_APPLICATION_FEE_PAID_HEADING = 'permits.page.fee.permit.fee.paid.total';
    public const PAYMENT_DUE_DATE_HEADING = 'permits.page.fee.payment.due.date';
    public const FEE_NON_REFUNDABLE_HEADING = 'permits.page.fee.permit.fee.non-refundable';
    public const AMOUNT_PAID_HEADING = 'permits.page.fee.permit.fee.amount-paid';
    public const AMOUNT_REMAINING_HEADING = 'permits.page.fee.permit.fee.amount-remaining';

    public const ALREADY_PAID_STATUS = 'permits.page.fee.permit.fee.already-paid';
    public const TO_BE_PAID_STATUS = 'permits.page.fee.permit.fee.to-be-paid';


    private TranslationHelperService $translator;

    /** @var EcmtNoOfPermits */
    private EcmtNoOfPermits $ecmtNoOfPermits;

    /** @var StatusFormatter */
    private StatusFormatter $statusFormatter;

    /** @var CurrencyFormatter */
    private CurrencyFormatter $currencyFormatter;

    /** @var UrlHelperService */
    private UrlHelperService $urlHelperService;

    /**
     * Create service instance
     *
     * @param TranslationHelperService $translator
     * @param EcmtNoOfPermits $ecmtNoOfPermits
     * @param StatusFormatter $statusFormatter
     * @param CurrencyFormatter $currencyFormatter
     * @param UrlHelperService $urlHelperService
     */
    public function __construct(
        TranslationHelperService $translator,
        EcmtNoOfPermits $ecmtNoOfPermits,
        StatusFormatter $statusFormatter,
        CurrencyFormatter $currencyFormatter,
        UrlHelperService $urlHelperService
    ) {
        $this->translator = $translator;
        $this->ecmtNoOfPermits = $ecmtNoOfPermits;
        $this->statusFormatter = $statusFormatter;
        $this->currencyFormatter = $currencyFormatter;
        $this->urlHelperService = $urlHelperService;
    }

    /**
     * Map IRHP application data for use on the fee summary page
     *
     * @param array $data input data
     *
     * @return array
     *
     * @throws RuntimeException
     */
    public function mapForDisplay(array $data): array
    {
        $applicationData = $data['application'];
        $irhpPermitTypeId = $applicationData['irhpPermitType']['id'];

        switch ($irhpPermitTypeId) {
            case RefData::IRHP_BILATERAL_PERMIT_TYPE_ID:
                $totalFeeAmount = $this->getTotalFeeAmount($data['feeBreakdown']);
                $outstandingFeeAmount = $applicationData['outstandingFeeAmount'];

                if ($outstandingFeeAmount == 0) {
                    $data['application']['warningMessage'] = 'permits.page.irhp-fee.message.total-already-paid';
                } elseif ($totalFeeAmount != $outstandingFeeAmount) {
                    $data['application']['warningMessage'] = 'permits.page.irhp-fee.message.part-paid';
                }
                $mappedFeeData = $this->getBilateralRows($applicationData, $totalFeeAmount, $outstandingFeeAmount);
                break;
            case RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID:
                $mappedFeeData = $this->getMultilateralRows($applicationData);
                break;
            case RefData::ECMT_REMOVAL_PERMIT_TYPE_ID:
                $data['application']['showFeeSummaryTitle'] = true;
                $mappedFeeData = $this->getEcmtRemovalRows($applicationData);
                break;
            case RefData::ECMT_PERMIT_TYPE_ID:
            case RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID:
                $data['application']['showFeeSummaryTitle'] = true;
                $data['application']['warningMessage'] = 'permits.page.irhp-fee.message';
                $data['application']['guidance'] = $this->getGuidanceData($applicationData);

                $mappedFeeData = $this->getEcmtShortTermRows($applicationData);
                break;
            default:
                throw new RuntimeException('Unsupported permit type id ' . $irhpPermitTypeId);
        }

        $data['application']['mappedFeeData'] = $mappedFeeData;
        $data['application']['prependTitle'] = $applicationData['irhpPermitType']['name']['description'];

        return $data;
    }

    /**
     * Get the html representing the guidance area of the page
     *
     * @param array $data
     *
     * @return array
     */
    private function getGuidanceData(array $data): array
    {
        if ($data['businessProcess']['id'] != RefData::BUSINESS_PROCESS_APSG) {
            return [];
        }

        $permitsRequired = $data['totalPermitsRequired'];
        $permitsAwarded = $data['totalPermitsAwarded'];

        $guidanceKey = ($permitsAwarded == $permitsRequired)
            ? 'markup-ecmt-fee-successful-hint' : 'markup-ecmt-fee-part-successful-hint';

        return [
            'value' => $this->translator->translateReplace(
                $guidanceKey,
                [$permitsAwarded, $permitsRequired]
            ),
            'disableHtmlEscape' => true
        ];
    }

    /**
     * Get the fee summary table content for bilateral type
     *
     * @param array $applicationData input data
     * @param int $totalFeeAmount
     * @param int $outstandingFeeAmount
     *
     * @return array
     */
    private function getBilateralRows(array $applicationData, int $totalFeeAmount, int $outstandingFeeAmount): array
    {
        $rows = [
            $this->getApplicationReferenceRow($applicationData),
            $this->getDateReceivedRow($applicationData),
            $this->getPermitTypeRow($applicationData),
            $this->getStandardNoOfPermitsRow($applicationData),
        ];

        if ($totalFeeAmount == $outstandingFeeAmount) {
            $rows[] = $this->getOutstandingFeeAmountRow($applicationData);
        } elseif ($outstandingFeeAmount == 0) {
            $rows[] = $this->getAmountPaidRow($totalFeeAmount);
        } else {
            $amountPaid = $totalFeeAmount - $outstandingFeeAmount;
            $rows[] = $this->getAmountPaidRow($amountPaid);
            $rows[] = $this->getAmountRemainingRow($outstandingFeeAmount);
        }

        return $rows;
    }

    /**
     * Get the fee summary table content for multilateral type
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getMultilateralRows(array $data): array
    {
        return [
            $this->getApplicationReferenceRow($data),
            $this->getDateReceivedRow($data),
            $this->getPermitTypeRow($data),
            $this->getStandardNoOfPermitsRow($data),
            $this->getOutstandingFeeAmountRow($data),
        ];
    }

    /**
     * Get the fee summary table content for ecmt removal type
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getEcmtRemovalRows(array $data): array
    {
        return [
            $this->getApplicationReferenceRow($data),
            $this->getDateReceivedRow($data),
            $this->getPermitTypeRow($data),
            $this->getFeePerPermitRow(
                $data,
                self::FEE_PER_PERMIT_HEADING,
                RefData::IRFO_GV_FEE_TYPE
            ),
            $this->getStandardNoOfPermitsRow($data),
            $this->getOutstandingFeeAmountRow($data),
        ];
    }

    /**
     * Get the fee summary table content for the ecmt short term type
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getEcmtShortTermRows(array $data): array
    {
        if ($data['isUnderConsideration']) {
            // under consideration has different content of the table
            return [
                $this->getPermitStatusRow($data),
                $this->getPermitTypeRow($data),
                $this->getStockValidityPeriodRow($data),
                $this->getApplicationReferenceRow($data),
                $this->getDateReceivedRow($data),
                $this->getEmissionsCatNoOfPermitsRequiredRow($data),
                $this->getTotalFeeRowUsingPermitsRequired(
                    $data,
                    RefData::IRHP_GV_APPLICATION_FEE_TYPE,
                    self::TOTAL_APPLICATION_FEE_PAID_HEADING
                ),
            ];
        } elseif ($data['isAwaitingFee']) {
            // accept/decline page has different content of the table

            return [
                $this->getPermitTypeRow($data),
                $this->getStockValidityPeriodRow($data),
                $this->getApplicationReferenceRow($data),
                $this->getEmissionsCatNoOfPermitsWantedRow($data),
                $this->getIssueFeePerPermitRow($data, RefData::IRHP_GV_ISSUE_FEE_TYPE),
                $this->getTotalFeeRowUsingPermitsWanted(
                    $data,
                    RefData::IRHP_GV_ISSUE_FEE_TYPE,
                    self::TOTAL_ISSUE_FEE_HEADING
                ),
                $this->getPaymentDueDateRow($data, RefData::IRHP_GV_ISSUE_FEE_TYPE),
            ];
        }

        return [
            $this->getPermitTypeRow($data),
            $this->getStockValidityPeriodRow($data),
            $this->getApplicationReferenceRow($data),
            $this->getDateReceivedRow($data),
            $this->getEmissionsCatNoOfPermitsRequiredRow($data),
            $this->getApplicationFeePerPermitRow($data, RefData::IRHP_GV_APPLICATION_FEE_TYPE),
            $this->getTotalFeeRowUsingPermitsRequired(
                $data,
                RefData::IRHP_GV_APPLICATION_FEE_TYPE,
                self::TOTAL_APPLICATION_FEE_HEADING
            ),
        ];
    }

    /**
     * Get the single table row content for a permit status row
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getPermitStatusRow(array $data): array
    {
        $statusFormatter = $this->statusFormatter;

        return [
            'key' => self::PERMIT_STATUS_HEADING,
            'value' => $statusFormatter($data['status']),
            'disableHtmlEscape' => true,
        ];
    }

    /**
     * Get the single table row content for a permit type row
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getPermitTypeRow(array $data): array
    {
        return [
            'key' => self::PERMIT_TYPE_HEADING,
            'value' => $data['irhpPermitType']['name']['description']
        ];
    }

    /**
     * Get the single table row content for a stock validity year row
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getStockValidityPeriodRow(array $data): array
    {
        $stock = $data['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];

        if (!empty($stock['periodNameKey'])) {
            return [
                'key' => self::PERMIT_PERIOD_HEADING,
                'value' => $stock['periodNameKey']
            ];
        }

        return [
            'key' => self::PERMIT_YEAR_HEADING,
            'value' => $stock['validityYear']
        ];
    }

    /**
     * Get the single table row content for an application reference row
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getApplicationReferenceRow(array $data): array
    {
        return [
            'key' => self::APP_REFERENCE_HEADING,
            'value' => $data['applicationRef']
        ];
    }

    /**
     * Get the single table row content for a date received row
     *
     * @param array $data input data
     *
     * @return array
     * @throws \Exception
     */
    private function getDateReceivedRow(array $data): array
    {
        $receivedDate = new DateTime($data['dateReceived']);

        return [
            'key' => self::APP_DATE_HEADING,
            'value' => $receivedDate->format('d F Y')
        ];
    }

    /**
     * Get the single table row content for a payment due date
     *
     * @param array $data input data
     * @param string $feeType
     *
     * @return array
     * @throws \Exception
     */
    private function getPaymentDueDateRow(array $data, $feeType)
    {
        $fee = $this->getFeeByTypeAndOptionalStatus($data['fees'], $feeType, RefData::FEE_STATUS_OUTSTANDING);

        return [
            'key' => self::PAYMENT_DUE_DATE_HEADING,
            'value' => !empty($fee['dueDate']) ? (new DateTime($fee['dueDate']))->format('d F Y') : ''
        ];
    }

    /**
     * Get the single table row content for a standard number of permits row
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getStandardNoOfPermitsRow(array $data)
    {
        return [
            'key' => self::NUM_PERMITS_HEADING,
            'value' => $data['permitsRequired'],
        ];
    }

    /**
     * Get the single table row content for a number of permits required row that uses emissions categories
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getEmissionsCatNoOfPermitsRequiredRow(array $data)
    {
        return $this->getEmissionsCatNoOfPermitsRow($data['irhpPermitApplications'][0]);
    }

    /**
     * Get the single table row content for a number of permits wanted row that uses emissions categories
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getEmissionsCatNoOfPermitsWantedRow(array $data)
    {
        $irhpPermitApplication = $data['irhpPermitApplications'][0];

        $ecmtNoOfPermitsData = [
            'requiredEuro5' => $irhpPermitApplication['euro5PermitsWanted'],
            'requiredEuro6' => $irhpPermitApplication['euro6PermitsWanted']
        ];

        return $this->getEmissionsCatNoOfPermitsRow($ecmtNoOfPermitsData);
    }

    /**
     * Get the single table row content for a number of permits row, using the supplied ecmtNoOfPermits data as input
     * into the mapper
     *
     * @param array $ecmtNoOfPermitsData
     *
     * @return array
     */
    private function getEmissionsCatNoOfPermitsRow(array $ecmtNoOfPermitsData)
    {
        $lines = $this->ecmtNoOfPermits->mapForDisplay($ecmtNoOfPermitsData);

        return [
            'key' => self::NUM_PERMITS_HEADING,
            'value' => implode('<br>', $lines),
            'disableHtmlEscape' => true,
        ];
    }

    /**
     * Get the single table row content for an application fee per permit row
     *
     * @param array $data input data
     * @param string $feeType
     *
     * @return array
     */
    private function getApplicationFeePerPermitRow(array $data, $feeType)
    {
        return $this->getFeePerPermitRow($data, self::APP_FEE_PER_PERMIT_HEADING, $feeType);
    }

    /**
     * Get the single table row content for an issue fee per permit row
     *
     * @param array $data input data
     * @param string $feeType
     *
     * @return array
     */
    private function getIssueFeePerPermitRow(array $data, $feeType)
    {
        return $this->getFeePerPermitRow($data, self::ISSUE_FEE_PER_PERMIT_HEADING, $feeType);
    }

    /**
     * Get the single table row content for a fee per permit row
     *
     * @param array $data input data
     * @param string $key
     * @param string $feeType
     *
     * @return array
     */
    private function getFeePerPermitRow(array $data, $key, $feeType)
    {
        return [
            'key' => $key,
            'value' => $this->getFeeAmountByType($data['fees'], $feeType),
            'isCurrency' => true
        ];
    }

    /**
     * Get the single table row content for an outstanding fee amount row
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getOutstandingFeeAmountRow($data)
    {
        $currencyFormatter = $this->currencyFormatter;

        return [
            'key' => self::FEE_TOTAL_HEADING,
            'value' => $this->translator->translateReplace(
                'permits.page.fee.permit.fee.non-refundable',
                [
                    $currencyFormatter($data['outstandingFeeAmount'])
                ]
            ),
        ];
    }

    /**
     * Get the single table row content for a total fee per permit row, calculating the total based on permits
     * required
     *
     * @param array $data input data
     * @param string $feeType
     * @param string $key
     *
     * @return array
     */
    private function getTotalFeeRowUsingPermitsRequired(array $data, $feeType, $key)
    {
        return $this->getTotalFeeRow($data, $feeType, $key, 'requiredEuro5', 'requiredEuro6');
    }

    /**
     * Get the single table row content for a total fee per permit row, calculating the total based on permits
     * wanted
     *
     * @param array $data input data
     * @param string $feeType
     * @param string $key
     *
     * @return array
     */
    private function getTotalFeeRowUsingPermitsWanted(array $data, $feeType, $key)
    {
        return $this->getTotalFeeRow($data, $feeType, $key, 'euro5PermitsWanted', 'euro6PermitsWanted');
    }

    /**
     * Get the single table row content for a total fee per permit row, calculating the total using the supplied
     * array keys for each emissions category
     *
     * @param array $data input data
     * @param string $feeType
     * @param string $key
     * @param string $euro5Key
     * @param string $euro6Key
     *
     * @return array
     */
    private function getTotalFeeRow(array $data, $feeType, $key, $euro5Key, $euro6Key)
    {
        $feePerPermit = $this->getFeeAmountByType($data['fees'], $feeType);
        $irhpPermitApplication = $data['irhpPermitApplications'][0];
        $requiredEuro5 = $irhpPermitApplication[$euro5Key];
        $requiredEuro6 = $irhpPermitApplication[$euro6Key];

        $totalFee = ($requiredEuro5 + $requiredEuro6) * $feePerPermit;

        $currencyFormatter = $this->currencyFormatter;
        $value =  $this->translator->translateReplace(
            'permits.page.fee.permit.fee.non-refundable',
            [
                $currencyFormatter($totalFee)
            ]
        );

        return [
            'key' => $key,
            'value' => $value,
        ];
    }

    /**
     * Get the single table row content for an amount paid row
     *
     * @param int $amountPaid
     *
     * @return array
     */
    private function getAmountPaidRow($amountPaid)
    {
        return $this->getAmountRow(
            self::AMOUNT_PAID_HEADING,
            $amountPaid,
            [
                'caption' => self::ALREADY_PAID_STATUS,
                'colour' => 'green'
            ]
        );
    }

    /**
     * Get the single table row content for an amount remaining row
     *
     * @param int $amountRemaining
     *
     * @return array
     */
    private function getAmountRemainingRow($amountRemaining)
    {
        return $this->getAmountRow(
            self::AMOUNT_REMAINING_HEADING,
            $amountRemaining,
            [
                'caption' => self::TO_BE_PAID_STATUS,
                'colour' => 'orange'
            ]
        );
    }

    /**
     * Get the single table row content for an amount-related row
     *
     * @param string $key
     * @param int $amount
     * @param array $status
     *
     * @return array
     */
    private function getAmountRow($key, $amount, array $status)
    {
        $currencyFormatter = $this->currencyFormatter;

        return [
            'key' => $key,
            'value' => $this->translator->translateReplace(
                'permits.page.fee.permit.fee.non-refundable',
                [
                    $currencyFormatter($amount)
                ]
            ),
            'status' => $status
        ];
    }

    /**
     * Get the fee amount corresponding to the specified fee type
     *
     * @param array $fees
     * @param string $feeTypeId
     *
     * @return int
     */
    private function getFeeAmountByType(array $fees, $feeTypeId)
    {
        $fee = $this->getFeeByTypeAndOptionalStatus($fees, $feeTypeId);

        if (isset($fee)) {
            return $fee['feeType']['fixedValue'];
        }
    }

    /**
     * Get the fee corresponding to the specified fee type
     *
     * @param array $fees
     * @param string $feeTypeId
     * @param string $statusId (optional)
     *
     * @return array
     */
    private function getFeeByTypeAndOptionalStatus(array $fees, $feeTypeId, $statusId = null)
    {
        foreach ($fees as $fee) {
            $statusMatches = true;
            if (!is_null($statusId)) {
                $statusMatches = $fee['feeStatus']['id'] == $statusId;
            }

            $feeType = $fee['feeType'];
            if ($feeType['feeType']['id'] == $feeTypeId && $statusMatches) {
                return $fee;
            }
        }
    }

    /**
     * Get the full amount payable using the fee breakdown data
     *
     * @param array $feeBreakdownData
     *
     * @return int
     */
    private function getTotalFeeAmount(array $feeBreakdownData)
    {
        $total = 0;

        foreach ($feeBreakdownData as $row) {
            $total += $row['total'];
        }

        return $total;
    }
}

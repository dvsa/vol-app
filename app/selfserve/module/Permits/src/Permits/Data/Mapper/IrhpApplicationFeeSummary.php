<?php

namespace Permits\Data\Mapper;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\View\Helper\CurrencyFormatter;
use Common\View\Helper\Status as StatusFormatter;
use DateTime;
use Permits\View\Helper\IrhpApplicationSection;
use RuntimeException;

/**
 * Mapper for the IRHP application fee summary page
 */
class IrhpApplicationFeeSummary
{
    const APP_REFERENCE_HEADING = 'permits.page.fee.application.reference';
    const APP_DATE_HEADING = 'permits.page.fee.application.date';
    const FEE_PER_PERMIT_HEADING = 'permits.irhp.fee-breakdown.fee-per-permit';
    const APP_FEE_PER_PERMIT_HEADING = 'permits.page.fee.application.fee.per.permit';
    const ISSUE_FEE_PER_PERMIT_HEADING = 'permits.page.fee.issue.fee.per.permit';
    const PERMIT_STATUS_HEADING = 'permits.page.fee.permit.status';
    const PERMIT_TYPE_HEADING = 'permits.page.fee.permit.type';
    const PERMIT_YEAR_HEADING = 'permits.page.fee.permit.year';
    const PERMIT_PERIOD_HEADING = 'permits.page.fee.permit.period';
    const NUM_PERMITS_HEADING = 'permits.page.fee.number.permits';
    const NUM_PERMITS_REQUIRED_HEADING = 'permits.page.fee.number.permits.required';
    const FEE_TOTAL_HEADING = 'permits.page.irhp-fee.permit.fee.total';
    const TOTAL_ISSUE_FEE_HEADING = 'permits.page.fee.permit.fee.issue.total';
    const TOTAL_APPLICATION_FEE_HEADING = 'permits.page.fee.permit.fee.total';
    const TOTAL_APPLICATION_FEE_PAID_HEADING = 'permits.page.fee.permit.fee.paid.total';
    const PAYMENT_DUE_DATE_HEADING = 'permits.page.fee.payment.due.date';
    const FEE_NON_REFUNDABLE_HEADING = 'permits.page.fee.permit.fee.non-refundable';

    /** @var TranslationHelperService */
    private $translator;

    /** @var EcmtNoOfPermits */
    private $ecmtNoOfPermits;

    /** @var StatusFormatter */
    private $statusFormatter;

    /** @var CurrencyFormatter */
    private $currencyFormatter;

    /** @var UrlHelperService */
    private $urlHelperService;

    /**
     * Create service instance
     *
     * @param TranslationHelperService $translator
     * @param EcmtNoOfPermits $ecmtNoOfPermits
     * @param StatusFormatter $statusFormatter
     * @param CurrencyFormatter $currencyFormatter
     * @param UrlHelperService $urlHelperService
     *
     * @return IrhpApplicationFeeSummary
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
    public function mapForDisplay(array $data)
    {
        $irhpPermitTypeId = $data['irhpPermitType']['id'];
        switch ($irhpPermitTypeId) {
            case RefData::IRHP_BILATERAL_PERMIT_TYPE_ID:
            case RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID:
                $mappedFeeData = $this->getBilateralMultilateralRows($data);
                break;
            case RefData::ECMT_REMOVAL_PERMIT_TYPE_ID:
                $data['showFeeSummaryTitle'] = true;
                $mappedFeeData = $this->getEcmtRemovalRows($data);
                break;
            case RefData::ECMT_PERMIT_TYPE_ID:
            case RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID:
                $data['showFeeSummaryTitle'] = true;
                $data['showWarningMessage'] = true;
                $data['guidance'] = $this->getGuidanceData($data);

                $mappedFeeData = $this->getEcmtShortTermRows($data);
                break;
            default:
                throw new RuntimeException('Unsupported permit type id ' . $irhpPermitTypeId);
        }

        $data['mappedFeeData'] = $mappedFeeData;
        $data['prependTitle'] = $data['irhpPermitType']['name']['description'];

        return $data;
    }

    /**
     * Get the html representing the guidance area of the page
     *
     * @param array $data
     *
     * @return array
     */
    private function getGuidanceData(array $data)
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
     * Get the fee summary table content for bilateral/multilateral types
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getBilateralMultilateralRows(array $data)
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
    private function getEcmtRemovalRows(array $data)
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
    private function getEcmtShortTermRows(array $data)
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
                $this->getEmissionsCatNoOfPermitsAwardedRow($data),
                $this->getIssueFeePerPermitRow($data, RefData::IRHP_GV_ISSUE_FEE_TYPE),
                $this->getTotalFeeRowUsingPermitsAwarded(
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
    private function getPermitStatusRow(array $data)
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
    private function getPermitTypeRow(array $data)
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
    private function getStockValidityPeriodRow(array $data)
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
    private function getApplicationReferenceRow(array $data)
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
     */
    private function getDateReceivedRow(array $data)
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
     */
    private function getPaymentDueDateRow(array $data, $feeType)
    {
        $fee = $this->getFeeByType($data['fees'], $feeType);

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
            'key' => self::NUM_PERMITS_REQUIRED_HEADING,
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
        return $this->getEmissionsCatNoOfPermitsRow(
            $data,
            $data['irhpPermitApplications'][0]
        );
    }

    /**
     * Get the single table row content for a number of permits awarded row that uses emissions categories
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getEmissionsCatNoOfPermitsAwardedRow(array $data)
    {
        $irhpPermitApplication = $data['irhpPermitApplications'][0];

        $ecmtNoOfPermitsData = [
            'requiredEuro5' => $irhpPermitApplication['euro5PermitsAwarded'],
            'requiredEuro6' => $irhpPermitApplication['euro6PermitsAwarded']
        ];

        return $this->getEmissionsCatNoOfPermitsRow($data, $ecmtNoOfPermitsData);
    }

    /**
     * Get the single table row content for a number of permits row, using the supplied ecmtNoOfPermits data as input
     * into the mapper
     *
     * @param array $data input data
     *
     * @return array
     */
    private function getEmissionsCatNoOfPermitsRow(array $data, array $ecmtNoOfPermitsData)
    {
        $lines = $this->ecmtNoOfPermits->mapForDisplay($ecmtNoOfPermitsData);

        if ($data['canViewCandidatePermits']) {
            $lines[] = sprintf(
                '<a href="%s">%s</a>',
                $this->urlHelperService->fromRoute(IrhpApplicationSection::ROUTE_UNPAID_PERMITS, [], [], true),
                $this->translator->translate('permits.page.view.permit.restrictions')
            );
        }

        return [
            'key' => $data['isAwaitingFee'] ? self::NUM_PERMITS_HEADING : self::NUM_PERMITS_REQUIRED_HEADING,
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
     * awarded
     *
     * @param array $data input data
     * @param string $feeType
     * @param string $key
     *
     * @return array
     */
    private function getTotalFeeRowUsingPermitsAwarded(array $data, $feeType, $key)
    {
        return $this->getTotalFeeRow($data, $feeType, $key, 'euro5PermitsAwarded', 'euro6PermitsAwarded');
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
     * Get the fee amount corresponding to the specified fee type
     *
     * @param array $fees
     * @param string $feeTypeId
     *
     * @return int
     */
    private function getFeeAmountByType(array $fees, $feeTypeId)
    {
        $fee = $this->getFeeByType($fees, $feeTypeId);

        if (isset($fee)) {
            return $fee['feeType']['fixedValue'];
        }
    }

    /**
     * Get the fee corresponding to the specified fee type
     *
     * @param array $fees
     * @param string $feeTypeId
     *
     * @return array
     */
    private function getFeeByType(array $fees, $feeTypeId)
    {
        foreach ($fees as $fee) {
            $feeType = $fee['feeType'];
            if ($feeType['feeType']['id'] == $feeTypeId) {
                return $fee;
            }
        }
    }
}

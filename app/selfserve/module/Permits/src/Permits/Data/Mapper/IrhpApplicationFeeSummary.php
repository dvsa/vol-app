<?php

namespace Permits\Data\Mapper;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\View\Helper\CurrencyFormatter;
use Common\View\Helper\Status as StatusFormatter;
use DateTime;
use RuntimeException;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * Mapper for the IRHP application fee summary page
 */
class IrhpApplicationFeeSummary
{
    const APP_REFERENCE_HEADING = 'permits.page.fee.application.reference';
    const APP_DATE_HEADING = 'permits.page.fee.application.date';
    const APP_FEE_PER_PERMIT_HEADING = 'permits.page.fee.application.fee.per.permit';
    const PERMIT_STATUS_HEADING = 'permits.page.fee.permit.status';
    const PERMIT_TYPE_HEADING = 'permits.page.fee.permit.type';
    const PERMIT_YEAR_HEADING = 'permits.page.fee.permit.year';
    const NUM_PERMITS_HEADING = 'permits.page.fee.number.permits';
    const FEE_TOTAL_HEADING = 'permits.page.irhp-fee.permit.fee.total';
    const TOTAL_APPLICATION_FEE_HEADING = 'permits.page.fee.permit.fee.total';
    const TOTAL_APPLICATION_FEE_PAID_HEADING = 'permits.page.fee.permit.fee.paid.total';
    const FEE_NON_REFUNDABLE_HEADING = 'permits.page.fee.permit.fee.non-refundable';

    /**
     * Map IRHP application data for use on the fee summary page
     *
     * @param array $data input data
     * @param TranslationHelperService $translator
     * @param Url $url
     *
     * @return array
     *
     * @throws RuntimeException
     */
    public static function mapForDisplay(array $data, TranslationHelperService $translator, Url $url)
    {
        $irhpPermitTypeId = $data['irhpPermitType']['id'];
        switch ($irhpPermitTypeId) {
            case RefData::IRHP_BILATERAL_PERMIT_TYPE_ID:
            case RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID:
            case RefData::ECMT_REMOVAL_PERMIT_TYPE_ID:
                $mappedFeeData = self::getBilateralMultilateralRemovalRows($data, $translator);
                break;
            case RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID:
                $data['showFeeSummaryTitle'] = true;
                $data['showWarningMessage'] = true;
                $mappedFeeData = self::getEcmtShortTermRows($data, $translator, $url);
                break;
            default:
                throw new RuntimeException('Unsupported permit type id ' . $irhpPermitTypeId);
        }

        $data['mappedFeeData'] = $mappedFeeData;

        return $data;
    }

    /**
     * Get the fee summary table content for bilateral/multilateral/removal types
     *
     * @param array $data input data
     * @param TranslationHelperService $translator
     *
     * @return array
     */
    private static function getBilateralMultilateralRemovalRows(array $data, TranslationHelperService $translator)
    {
        return [
            self::getApplicationReferenceRow($data),
            self::getDateReceivedRow($data),
            self::getPermitTypeRow($data),
            self::getStandardNoOfPermitsRow($data),
            self::getOutstandingFeeAmountRow($data, $translator),
        ];
    }

    /**
     * Get the fee summary table content for the ecmt short term type
     *
     * @param array $data input data
     * @param TranslationHelperService $translator
     * @param Url $url
     *
     * @return array
     */
    private static function getEcmtShortTermRows(array $data, TranslationHelperService $translator, Url $url)
    {
        if ($data['isUnderConsideration']) {
            // under consideration has different content of the table
            return [
                self::getPermitStatusRow($data),
                self::getPermitTypeRow($data),
                self::getStockValidityYearRow($data),
                self::getApplicationReferenceRow($data),
                self::getDateReceivedRow($data),
                self::getEmissionsCatNoOfPermitsRow($data, $translator, $url),
                self::getEmissionsCatTotalApplicationFeeRow($data, 'IRHPGVAPP', $translator),
            ];
        }

        return [
            self::getPermitTypeRow($data),
            self::getStockValidityYearRow($data),
            self::getApplicationReferenceRow($data),
            self::getDateReceivedRow($data),
            self::getEmissionsCatNoOfPermitsRow($data, $translator, $url),
            self::getApplicationFeePerPermitRow($data, 'IRHPGVAPP'),
            self::getEmissionsCatTotalApplicationFeeRow($data, 'IRHPGVAPP', $translator),
        ];
    }

    /**
     * Get the single table row content for a permit status row
     *
     * @param array $data input data
     *
     * @return array
     */
    private static function getPermitStatusRow(array $data)
    {
        $statusFormatter = new StatusFormatter();

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
    private static function getPermitTypeRow(array $data)
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
    private static function getStockValidityYearRow(array $data)
    {
        $stockValidTo = $data['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock']['validTo'];
        $validityYear = (new DateTime($stockValidTo))->format('Y');

        return [
            'key' => self::PERMIT_YEAR_HEADING,
            'value' => $validityYear
        ];
    }

    /**
     * Get the single table row content for an application reference row
     *
     * @param array $data input data
     *
     * @return array
     */
    private static function getApplicationReferenceRow(array $data)
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
    private static function getDateReceivedRow(array $data)
    {
        $receivedDate = new DateTime($data['dateReceived']);

        return [
            'key' => self::APP_DATE_HEADING,
            'value' => $receivedDate->format('d F Y')
        ];
    }

    /**
     * Get the single table row content for a standard number of permits row
     *
     * @param array $data input data
     *
     * @return array
     */
    private static function getStandardNoOfPermitsRow(array $data)
    {
        return [
            'key' => self::NUM_PERMITS_HEADING,
            'value' => $data['permitsRequired'],
        ];
    }

    /**
     * Get the single table row content for a number of permits row that uses emissions categories
     *
     * @param array $data input data
     * @param TranslationHelperService $translator
     * @param Url $url
     *
     * @return array
     */
    private static function getEmissionsCatNoOfPermitsRow(array $data, TranslationHelperService $translator, Url $url)
    {
        $irhpPermitApplication = $data['irhpPermitApplications'][0];
        $lines = EcmtNoOfPermits::mapForDisplay($irhpPermitApplication, $translator, $url);

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
    private static function getApplicationFeePerPermitRow(array $data, $feeType)
    {
        return [
            'key' => self::APP_FEE_PER_PERMIT_HEADING,
            'value' => self::getFeeAmountByType($data['fees'], $feeType),
            'isCurrency' => true
        ];
    }

    /**
     * Get the single table row content for an outstanding fee amount row
     *
     * @param array $data input data
     * @param TranslationHelperService $translator
     *
     * @return array
     */
    private static function getOutstandingFeeAmountRow($data, TranslationHelperService $translator)
    {
        $currencyFormatter = new CurrencyFormatter();

        return [
            'key' => self::FEE_TOTAL_HEADING,
            'value' => $translator->translateReplace(
                'permits.page.fee.permit.fee.non-refundable',
                [
                    $currencyFormatter($data['outstandingFeeAmount'])
                ]
            ),
        ];
    }

    /**
     * Get the single table row content for a total application fee per permit row based on emissions categories
     *
     * @param array $data input data
     * @param string $feeType
     * @param TranslationHelperService $translator
     *
     * @return array
     */
    private static function getEmissionsCatTotalApplicationFeeRow(
        array $data,
        $feeType,
        TranslationHelperService $translator
    ) {
        $feePerPermit = self::getFeeAmountByType($data['fees'], $feeType);
        $irhpPermitApplication = $data['irhpPermitApplications'][0];
        $requiredEuro5 = $irhpPermitApplication['requiredEuro5'];
        $requiredEuro6 = $irhpPermitApplication['requiredEuro6'];

        $totalApplicationFee = ($requiredEuro5 + $requiredEuro6) * $feePerPermit;
        $currencyFormatter = new CurrencyFormatter();

        $value =  $translator->translateReplace(
            'permits.page.fee.permit.fee.non-refundable',
            [
                $currencyFormatter($totalApplicationFee)
            ]
        );

        return [
            'key' => $data['isUnderConsideration']
                ? self::TOTAL_APPLICATION_FEE_PAID_HEADING : self::TOTAL_APPLICATION_FEE_HEADING,
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
    private static function getFeeAmountByType(array $fees, $feeTypeId)
    {
        foreach ($fees as $fee) {
            $feeType = $fee['feeType'];
            if ($feeType['feeType']['id'] == $feeTypeId) {
                return $feeType['fixedValue'];
            }
        }
    }
}

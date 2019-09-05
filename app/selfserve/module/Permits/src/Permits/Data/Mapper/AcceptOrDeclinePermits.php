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
     * @param TranslationHelperService $translator
     * @param Url $url
     *
     * @return array
     */
    public static function mapForDisplay(array $data, TranslationHelperService $translator, Url $url)
    {
        $data = ApplicationFees::mapForDisplay($data, $translator, $url);

        $summaryData = [
            self::getApplicationReferenceRow($data),
            self::getPermitTypeRow($data),
            self::getPermitYearRow($data),
            self::getNoOfPermitsRow($data, $translator, $url),
        ];

        if ($data['hasOutstandingFees']) {
            $summaryData[] = self::getFeePerPermitRow($data);
            $summaryData[] = self::getTotalFeeRow($data, $translator);
        }

        $summaryData[] = self::getDueDateRow($data);

        $data['title'] = self::getTitleKey($data);
        $data['summaryData'] = $summaryData;
        $data['guidance'] = self::getGuidanceData($data, $translator);

        return $data;
    }

    /**
     * Get the table row data corresponding to an application reference row
     *
     * @param array $data
     *
     * @return array
     */
    private static function getApplicationReferenceRow(array $data)
    {
        return [
            'key' => 'permits.page.fee.application.reference',
            'value' => $data['applicationRef']
        ];
    }

    /**
     * Get the table row data corresponding to a permit type row
     *
     * @param array $data
     *
     * @return array
     */
    private static function getPermitTypeRow(array $data)
    {
        return [
            'key' => 'permits.page.ecmt.consideration.permit.type',
            'value' => $data['permitType']['description']
        ];
    }

    /**
     * Get the table row data corresponding to a permit year row
     *
     * @param array $data
     *
     * @return array
     */
    private static function getPermitYearRow(array $data)
    {
        $firstIrhpPermitApplication = $data['irhpPermitApplications'][0];
        $irhpPermitStock = $firstIrhpPermitApplication['irhpPermitWindow']['irhpPermitStock'];

        return [
            'key' => 'permits.page.ecmt.consideration.permit.year',
            'value' => $irhpPermitStock['validityYear'],
        ];
    }

    /**
     * Get the table row data corresponding to a number of permits row
     *
     * @param array $data
     * @param TranslationHelperService $translator
     * @param Url $url
     *
     * @return array
     */
    private static function getNoOfPermitsRow(array $data, TranslationHelperService $translator, Url $url)
    {
        $firstIrhpPermitApplication = $data['irhpPermitApplications'][0];

        return [
            'key' => 'permits.page.fee.number.permits',
            'value' => self::getNoOfPermitsValue($firstIrhpPermitApplication, $translator, $url),
            'disableHtmlEscape' => true
        ];
    }

    /**
     * Get the table row data corresponding to a fee per permit row
     *
     * @param array $data
     *
     * @return array
     */
    private static function getFeePerPermitRow(array $data)
    {
        return [
            'key' => 'permits.page.ecmt.fee-part-successful.issuing.fee',
            'value' => $data['issueFee'],
            'isCurrency' => true
        ];
    }

    /**
     * Get the table row data corresponding to a total fee row
     *
     * @param array $data
     * @param TranslationHelperService $translator
     *
     * @return array
     */
    private static function getTotalFeeRow(array $data, TranslationHelperService $translator)
    {
        $currency = new CurrencyFormatter();

        return [
            'key' => 'permits.page.ecmt.fee-part-successful.issuing.fee.total',
            'value' => $translator->translateReplace(
                'permits.page.ecmt.fee-part-successful.fee.total.value',
                [
                    $currency($data['totalFee'])
                ]
            )
        ];
    }

    /**
     * Get the table row data corresponding to a due date row
     *
     * @param array $data
     *
     * @return array
     */
    private static function getDueDateRow(array $data)
    {
        $dueDateKey = 'waived.paid.permits.page.ecmt.fee-part-successful.payment.due';
        if ($data['hasOutstandingFees']) {
            $dueDateKey = 'permits.page.ecmt.fee-part-successful.payment.due';
        }

        return [
            'key' => $dueDateKey,
            'value' => date(\DATE_FORMAT, strtotime($data['dueDate']))
        ];
    }

    /**
     * Get the html representing the number of permits
     *
     * @param array $firstIrhpPermitApplication
     * @param TranslationHelperService $translator
     * @param Url $url
     *
     * @return array
     */
    private static function getNoOfPermitsValue(
        array $firstIrhpPermitApplication,
        TranslationHelperService $translator,
        Url $url
    ) {
        $ecmtNoOfPermitsData = [
            'requiredEuro5' => $firstIrhpPermitApplication['euro5PermitsAwarded'],
            'requiredEuro6' => $firstIrhpPermitApplication['euro6PermitsAwarded']
        ];

        $permitsRequiredLines = EcmtNoOfPermits::mapForDisplay($ecmtNoOfPermitsData, $translator, $url);

        $permitsRequiredLines[] = sprintf(
            '<a href="%s">%s</a>',
            $url->fromRoute(EcmtSection::ROUTE_ECMT_UNPAID_PERMITS, [], [], true),
            $translator->translate('permits.page.ecmt.fee-part-successful.view.permit.restrictions')
        );

        return implode('<br>', $permitsRequiredLines);
    }

    /**
     * Get the translation key representing the page title
     *
     * @param array $data
     *
     * @return string
     */
    private static function getTitleKey(array $data)
    {
        $titleKey = 'waived-paid-permits.page.fee-part-successful.title';
        if ($data['hasOutstandingFees']) {
            $titleKey = 'permits.page.fee-part-successful.title';
        }

        return $titleKey;
    }

    /**
     * Get the html representing the guidance area of the page
     *
     * @param array $data
     * @param TranslationHelperService $translator
     *
     * @return array
     */
    private static function getGuidanceData(array $data, TranslationHelperService $translator)
    {
        $permitsAwarded = $data['irhpPermitApplications'][0]['permitsAwarded'];
        $permitsRequired = $data['totalPermitsRequired'];

        $guidanceKey = 'markup-ecmt-fee-part-successful-hint';
        if ($permitsAwarded == $permitsRequired) {
            $guidanceKey = 'markup-ecmt-fee-successful-hint';
        }

        return [
            'value' => $translator->translateReplace(
                $guidanceKey,
                [$permitsAwarded, $permitsRequired]
            ),
            'disableHtmlEscape' => true
        ];
    }
}

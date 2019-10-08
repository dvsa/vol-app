<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\View\Helper\CurrencyFormatter;
use Permits\View\Helper\EcmtSection;

/**
 * Mapper for the FeePartSuccessful / Accept or Decline page
 */
class AcceptOrDeclinePermits
{
    /** @var TranslationHelperService */
    private $translator;

    /** @var UrlHelperService */
    private $urlHelperService;

    /** @var ApplicationFees */
    private $applicationFees;

    /** @var CurrencyFormatter */
    private $currencyFormatter;

    /** @var EcmtNoOfPermits */
    private $ecmtNoOfPermits;

    /**
     * Create service instance
     *
     * @param TranslationHelperService $translator
     * @param UrlHelperService $urlHelperService
     * @param ApplicationFees $applicationFees
     * @param CurrencyFormatter $currencyFormatter
     * @param EcmtNoOfPermits $ecmtNoOfPermits
     *
     * @return AcceptOrDeclinePermits
     */
    public function __construct(
        TranslationHelperService $translator,
        UrlHelperService $urlHelperService,
        ApplicationFees $applicationFees,
        CurrencyFormatter $currencyFormatter,
        EcmtNoOfPermits $ecmtNoOfPermits
    ) {
        $this->translator = $translator;
        $this->urlHelperService = $urlHelperService;
        $this->applicationFees = $applicationFees;
        $this->currencyFormatter = $currencyFormatter;
        $this->ecmtNoOfPermits = $ecmtNoOfPermits;
    }

    /**
     * Maps data appropriately for the Definition List on the FeePartSuccessful page
     *
     * @param array $data an array of data retrieved from the backend
     *
     * @return array
     */
    public function mapForDisplay(array $data)
    {
        $data = $this->applicationFees->mapForDisplay($data);

        $summaryData = [
            $this->getApplicationReferenceRow($data),
            $this->getPermitTypeRow($data),
            $this->getPermitYearRow($data),
            $this->getNoOfPermitsRow($data),
        ];

        if ($data['hasOutstandingFees']) {
            $summaryData[] = $this->getFeePerPermitRow($data);
            $summaryData[] = $this->getTotalFeeRow($data);
        }

        $summaryData[] = $this->getDueDateRow($data);

        $data['title'] = $this->getTitleKey($data);
        $data['summaryData'] = $summaryData;
        $data['guidance'] = $this->getGuidanceData($data);

        return $data;
    }

    /**
     * Get the table row data corresponding to an application reference row
     *
     * @param array $data
     *
     * @return array
     */
    private function getApplicationReferenceRow(array $data)
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
    private function getPermitTypeRow(array $data)
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
    private function getPermitYearRow(array $data)
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
     *
     * @return array
     */
    private function getNoOfPermitsRow(array $data)
    {
        $firstIrhpPermitApplication = $data['irhpPermitApplications'][0];

        return [
            'key' => 'permits.page.fee.number.permits',
            'value' => $this->getNoOfPermitsValue($firstIrhpPermitApplication),
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
    private function getFeePerPermitRow(array $data)
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
     *
     * @return array
     */
    private function getTotalFeeRow(array $data)
    {
        $currencyFormatter = $this->currencyFormatter;

        return [
            'key' => 'permits.page.ecmt.fee-part-successful.issuing.fee.total',
            'value' => $this->translator->translateReplace(
                'permits.page.ecmt.fee-part-successful.fee.total.value',
                [
                    $currencyFormatter($data['totalFee'])
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
    private function getDueDateRow(array $data)
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
     *
     * @return array
     */
    private function getNoOfPermitsValue(array $firstIrhpPermitApplication)
    {
        $ecmtNoOfPermitsData = [
            'requiredEuro5' => $firstIrhpPermitApplication['euro5PermitsAwarded'],
            'requiredEuro6' => $firstIrhpPermitApplication['euro6PermitsAwarded']
        ];

        $permitsRequiredLines = $this->ecmtNoOfPermits->mapForDisplay($ecmtNoOfPermitsData);

        $permitsRequiredLines[] = sprintf(
            '<a href="%s">%s</a>',
            $this->urlHelperService->fromRoute(EcmtSection::ROUTE_ECMT_UNPAID_PERMITS, [], [], true),
            $this->translator->translate('permits.page.view.permit.restrictions')
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
    private function getTitleKey(array $data)
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
     *
     * @return array
     */
    private function getGuidanceData(array $data)
    {
        $permitsAwarded = $data['irhpPermitApplications'][0]['permitsAwarded'];
        $permitsRequired = $data['totalPermitsRequired'];

        $guidanceKey = 'markup-ecmt-fee-part-successful-hint';
        if ($permitsAwarded == $permitsRequired) {
            $guidanceKey = 'markup-ecmt-fee-successful-hint';
        }

        return [
            'value' => $this->translator->translateReplace(
                $guidanceKey,
                [$permitsAwarded, $permitsRequired]
            ),
            'disableHtmlEscape' => true
        ];
    }
}

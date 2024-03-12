<?php

namespace Permits\View\Helper;

use Common\RefData;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Model\ViewModel;

/**
 * Generate an IRHP application section list
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class IrhpApplicationSection extends AbstractHelper
{
    public const ROUTE_PERMITS = 'permits';
    public const ROUTE_TYPE = 'permits/type';
    public const ROUTE_YEAR = 'permits/year';
    public const ROUTE_STOCK = 'permits/stock';
    public const ROUTE_WINDOW_CLOSED = 'permits/window-closed';
    public const ROUTE_PERMITS_EXHAUSTED = 'permits/exhausted';
    public const ROUTE_MAX_PERMITTED_REACHED_FOR_STOCK = 'permits/max-permitted-reached-for-stock';
    public const ROUTE_MAX_PERMITTED_REACHED_FOR_TYPE ='permits/max-permitted-reached-for-type';
    public const ROUTE_NOT_ELIGIBLE = 'permits/not-eligible';
    public const ROUTE_NO_LICENCES = 'permits/no-licences';
    public const ROUTE_ADD_LICENCE = 'permits/add-licence';
    public const ROUTE_APPLICATION_OVERVIEW = 'permits/application';
    public const ROUTE_COUNTRIES = 'permits/application/countries';
    public const ROUTE_COUNTRIES_CONFIRMATION = 'permits/application/countries-confirmation';
    public const ROUTE_IPA_QUESTION = 'permits/application/ipa/question';
    public const ROUTE_IPA_CHECK_ANSWERS = 'permits/application/ipa/check-answers';
    public const ROUTE_NO_OF_PERMITS = 'permits/application/no-of-permits';
    public const ROUTE_CHECK_ANSWERS = 'permits/application/check-answers';
    public const ROUTE_DECLARATION = 'permits/application/declaration';
    public const ROUTE_QUESTION = 'permits/application/question';
    public const ROUTE_FEE = 'permits/application/fee';
    public const ROUTE_UNDER_CONSIDERATION = 'permits/application/under-consideration';
    public const ROUTE_SUBMITTED = 'permits/application/submitted';
    public const ROUTE_AWAITING_FEE = 'permits/application/awaiting-fee';
    public const ROUTE_UNPAID_PERMITS = 'permits/application/unpaid-permits';
    public const ROUTE_PAYMENT_ACTION = 'permits/application/payment';
    public const ROUTE_PAYMENT_RESULT_ACTION = 'permits/application/payment-result';
    public const ROUTE_ESSENTIAL_INFORMATION = 'permits/application/essential-information';
    public const ROUTE_PERIOD = 'permits/application/period';
    public const ROUTE_CANDIDATE_SELECTION = 'permits/application/candidate-selection';

    public const ROUTE_CANCEL_APPLICATION = 'permits/application/cancel';
    public const ROUTE_CANCEL_CONFIRMATION = self::ROUTE_CANCEL_APPLICATION . '/confirmation';
    public const ROUTE_WITHDRAW_APPLICATION = 'permits/application/withdraw';
    public const ROUTE_WITHDRAW_CONFIRMATION = self::ROUTE_WITHDRAW_APPLICATION . '/confirmation';
    public const ROUTE_DECLINE_APPLICATION = 'permits/application/decline';
    public const ROUTE_DECLINE_CONFIRMATION = self::ROUTE_DECLINE_APPLICATION . '/confirmation';

    public const ROUTE_PRINT_RECEIPT = 'permits/print-receipt';

    /**
     * list of overview routes and the field denoting completion status
     */
    public const ROUTE_ORDER = [
        RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID => [
            self::ROUTE_NO_OF_PERMITS => 'permitsRequired',
            self::ROUTE_CHECK_ANSWERS => 'checkedAnswers',
            self::ROUTE_DECLARATION => 'declaration',
        ],
    ];

    /**
     * @todo pasted here temporarily while getting this working - move to a reusable helper/formatter
     */
    public const SECTION_COMPLETION_CANNOT_START = 'section_sts_csy';
    public const SECTION_COMPLETION_NOT_STARTED = 'section_sts_nys';
    public const SECTION_COMPLETION_INCOMPLETE = 'section_sts_inc';
    public const SECTION_COMPLETION_COMPLETED = 'section_sts_com';

    /**
     * @todo pasted here temporarily while getting this working - move to a reusable helper/formatter
     */
    public const COMPLETION_STATUS = [
        self::SECTION_COMPLETION_CANNOT_START => 'Can\'t start yet',
        self::SECTION_COMPLETION_NOT_STARTED => 'Not started yet',
        self::SECTION_COMPLETION_INCOMPLETE => 'Incomplete',
        self::SECTION_COMPLETION_COMPLETED => 'Completed',
    ];

    /**
     * @todo pasted here temporarily while getting this working - move to a reusable helper/formatter
     */
    public const COMPLETION_STATUS_COLOUR = [
        self::SECTION_COMPLETION_CANNOT_START => 'grey',
        self::SECTION_COMPLETION_NOT_STARTED => 'grey',
        self::SECTION_COMPLETION_INCOMPLETE => 'orange',
        self::SECTION_COMPLETION_COMPLETED => 'green',
    ];

    /**
     * Currently returns an array of sections - could be much better, but this is part 1...
     *
     * @todo investigate (do we need) a separate view partial, currently sharing/reusing view from licence applications
     * @todo handle ordering of steps in something dedicated to the job
     *
     * @param array $application    Application data
     * @param array $questionAnswer Question/Answer
     *
     * @return array
     */
    public function __invoke(array $application, array $questionAnswer = []): array
    {
        if (!empty($application['irhpPermitType']['isApplicationPathEnabled'])) {
            // the Q&A solution
            return $this->createSectionsForApplicationPath($application, $questionAnswer);
        }

        // this is kept for backward compatibility only until everything is migrated to the Q&A solution
        if (!isset($application['irhpPermitType']['id'])
            || !isset(self::ROUTE_ORDER[$application['irhpPermitType']['id']])
        ) {
            return [];
        }

        $sections = [];

        foreach (self::ROUTE_ORDER[$application['irhpPermitType']['id']] as $route => $field) {
            $sections[] = $this->createSection(
                'section.name.' . str_replace('permits/', '', $route),
                $application['sectionCompletion'][$field],
                $route,
                ['id' => $application['id']]
            );
        }

        return $sections;
    }

    /**
     * Create sections for the application path
     *
     * @param array $application    Application
     * @param array $questionAnswer Question/Answer
     *
     * @return array
     */
    private function createSectionsForApplicationPath(array $application, array $questionAnswer): array
    {
        $sections = [];

        // Q&A sections
        foreach ($questionAnswer as $data) {
            $route = self::ROUTE_QUESTION;
            $routeParams = [
                'id' => $application['id'],
                'slug' => $data['slug'],
            ];

            switch ($data['slug']) {
                case 'custom-check-answers':
                    $route = self::ROUTE_CHECK_ANSWERS;
                    $routeParams = [
                        'id' => $application['id'],
                    ];
                    break;
                case 'custom-declaration':
                    $route = self::ROUTE_DECLARATION;
                    $routeParams = [
                        'id' => $application['id'],
                    ];
                    break;
            }

            $sections[] = $this->createSection(
                $data['questionShort'],
                $data['status'],
                $route,
                $routeParams
            );
        }

        return $sections;
    }

    /**
     * create a section
     *
     * @param string $name        section name
     * @param string $status      status
     * @param string $route       route
     * @param array  $routeParams route params
     *
     * @return ViewModel
     */
    private function createSection(string $name, string $status, string $route, array $routeParams): ViewModel
    {
        $section = new ViewModel();
        $section->setTemplate('partials/overview_section');
        $section->setVariable('enabled', $status !== self::SECTION_COMPLETION_CANNOT_START);
        $section->setVariable('status', self::COMPLETION_STATUS[$status]);
        $section->setVariable('statusColour', self::COMPLETION_STATUS_COLOUR[$status]);
        $section->setVariable('name', $name);
        $section->setVariable('route', $route);
        $section->setVariable('routeParams', $routeParams);

        return $section;
    }
}

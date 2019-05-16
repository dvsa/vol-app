<?php

namespace Permits\View\Helper;

use Common\RefData;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

/**
 * Generate an IRHP application section list
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class IrhpApplicationSection extends AbstractHelper
{
    const ROUTE_PERMITS = 'permits';
    const ROUTE_TYPE = 'permits/type';
    const ROUTE_ADD_LICENCE = 'permits/add-licence';
    const ROUTE_APPLICATION_OVERVIEW = 'permits/application';
    const ROUTE_LICENCE = 'permits/application/licence';
    const ROUTE_LICENCE_CONFIRM_CHANGE = 'permits/application/licence/change';
    const ROUTE_EMISSIONS = 'permits/application/emissions';
    const ROUTE_COUNTRIES = 'permits/application/countries';
    const ROUTE_NO_OF_PERMITS = 'permits/application/no-of-permits';
    const ROUTE_CHECK_ANSWERS = 'permits/application/check-answers';
    const ROUTE_DECLARATION = 'permits/application/declaration';
    const ROUTE_QUESTION = 'permits/application/question';
    const ROUTE_FEE = 'permits/application/fee';
    const ROUTE_SUBMITTED = 'permits/application/submitted';
    const ROUTE_PAYMENT_ACTION = 'permits/application/payment';
    const ROUTE_PAYMENT_RESULT_ACTION = 'permits/application/payment-result';

    const ROUTE_CANCEL_APPLICATION = 'permits/application/cancel';
    const ROUTE_CANCEL_CONFIRMATION = self::ROUTE_CANCEL_APPLICATION . '/confirmation';
    const ROUTE_WITHDRAW_APPLICATION = 'permits/application/withdraw';
    const ROUTE_WITHDRAW_CONFIRMATION = self::ROUTE_WITHDRAW_APPLICATION . '/confirmation';
    const ROUTE_DECLINE_APPLICATION = 'permits/application/decline';
    const ROUTE_DECLINE_CONFIRMATION = self::ROUTE_DECLINE_APPLICATION . '/confirmation';

    const ROUTE_PRINT_RECEIPT = 'permits/print-receipt';

    /**
     * list of overview routes and the field denoting completion status
     */
    const ROUTE_ORDER = [
        RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID => [
            self::ROUTE_LICENCE => 'licence',
            self::ROUTE_EMISSIONS => 'emissions',
            self::ROUTE_NO_OF_PERMITS => 'permitsRequired',
            self::ROUTE_CHECK_ANSWERS => 'checkedAnswers',
            self::ROUTE_DECLARATION => 'declaration',
        ],
        RefData::IRHP_BILATERAL_PERMIT_TYPE_ID => [
            self::ROUTE_LICENCE => 'licence',
            self::ROUTE_COUNTRIES => 'countries',
            self::ROUTE_NO_OF_PERMITS => 'permitsRequired',
            self::ROUTE_CHECK_ANSWERS => 'checkedAnswers',
            self::ROUTE_DECLARATION => 'declaration',
        ],
        RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID => [
            self::ROUTE_LICENCE => 'licence',
            self::ROUTE_NO_OF_PERMITS => 'permitsRequired',
            self::ROUTE_CHECK_ANSWERS => 'checkedAnswers',
            self::ROUTE_DECLARATION => 'declaration',
        ],
    ];

    /**
     * @todo pasted here temporarily while getting this working - move to a reusable helper/formatter
     */
    const SECTION_COMPLETION_CANNOT_START = 'section_sts_csy';
    const SECTION_COMPLETION_NOT_STARTED = 'section_sts_nys';
    const SECTION_COMPLETION_COMPLETED = 'section_sts_com';

    /**
     * @todo pasted here temporarily while getting this working - move to a reusable helper/formatter
     */
    const COMPLETION_STATUS = [
        self::SECTION_COMPLETION_CANNOT_START => 'Can\'t start yet',
        self::SECTION_COMPLETION_NOT_STARTED => 'Not started yet',
        self::SECTION_COMPLETION_COMPLETED => 'Completed',
    ];

    /**
     * @todo pasted here temporarily while getting this working - move to a reusable helper/formatter
     */
    const COMPLETION_STATUS_COLOUR = [
        self::SECTION_COMPLETION_CANNOT_START => 'grey',
        self::SECTION_COMPLETION_NOT_STARTED => 'grey',
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
                case 'custom-licence':
                    $route = self::ROUTE_LICENCE;
                    $routeParams = [
                        'id' => $application['id'],
                    ];
                    break;
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
                $data['question'],
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

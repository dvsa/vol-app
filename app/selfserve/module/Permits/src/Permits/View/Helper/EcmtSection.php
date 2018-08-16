<?php

namespace Permits\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

/**
 * Generate the ECMT section list
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class EcmtSection extends AbstractHelper
{
    const ROUTE_APPLICATION_OVERVIEW = 'application-overview';
    const ROUTE_ECMT_LICENCE = 'ecmt-licence';
    const ROUTE_ECMT_EURO6 = 'ecmt-euro6';
    const ROUTE_ECMT_CABOTAGE = 'ecmt-cabotage';
    const ROUTE_ECMT_COUNTRIES = 'ecmt-countries';
    const ROUTE_ECMT_TRIPS = 'ecmt-trips';
    const ROUTE_ECMT_INTERNATIONAL_JOURNEY = 'ecmt-international-journey';
    const ROUTE_ECMT_SECTORS = 'ecmt-sectors';
    const ROUTE_ECMT_NO_OF_PERMITS = 'ecmt-no-of-permits';
    const ROUTE_ECMT_CHECK_ANSWERS = 'ecmt-check-answers';
    const ROUTE_ECMT_DECLARATION = 'ecmt-declaration';
    const ROUTE_ECMT_FEE = 'ecmt-fee';
    const ROUTE_ECMT_SUBMITTED = 'ecmt-submitted';
    const ROUTE_ECMT_CONFIRM_CHANGE = 'ecmt-change-licence';
    const ROUTE_ECMT_GUIDANCE = 'ecmt-guidance';

    //cancellation
    const ROUTE_ECMT_CANCEL_APPLICATION = 'ecmt-cancel-application';
    const ROUTE_ECMT_CANCEL_CONFIRMATION = self::ROUTE_ECMT_CANCEL_APPLICATION . '/confirmation';

    //withdraw
    const ROUTE_ECMT_WITHDRAW_APPLICATION = 'ecmt-withdraw-application';
    const ROUTE_ECMT_WITHDRAW_CONFIRMATION = self::ROUTE_ECMT_WITHDRAW_APPLICATION . '/confirmation';

    /**
     * list of overview routes and the field denoting completion status
     */
    const ROUTE_ORDER = [
        self:: ROUTE_ECMT_LICENCE => 'licence',
        self:: ROUTE_ECMT_EURO6 => 'emissions',
        self:: ROUTE_ECMT_CABOTAGE => 'cabotage',
        self:: ROUTE_ECMT_COUNTRIES => 'countrys',
        self:: ROUTE_ECMT_NO_OF_PERMITS => 'permitsRequired',
        self:: ROUTE_ECMT_TRIPS => 'trips',
        self:: ROUTE_ECMT_INTERNATIONAL_JOURNEY => 'internationalJourneys',
        self:: ROUTE_ECMT_SECTORS => 'sectors',
    ];

    /**
     * list of overview routes that are used for confirmation
     */
    const CONFIRMATION_ROUTE_ORDER = [
        self:: ROUTE_ECMT_CHECK_ANSWERS => 'checkedAnswers',
        self:: ROUTE_ECMT_DECLARATION => 'declaration',
    ];

    /**
     * @todo pasted here temporarily while getting this working - move to a reusable helper/formatter
     */
    const SECTION_COMPLETION_CANNOT_START = 'ecmt_section_sts_csy';
    const SECTION_COMPLETION_NOT_STARTED = 'ecmt_section_sts_nys';
    const SECTION_COMPLETION_COMPLETED = 'ecmt_section_sts_com';

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
     * @todo messy/rushed - must be improved and made more robust
     * @todo investigate (do we need) a separate view partial, currently sharing/reusing view from licence applications
     * @todo handle ordering of steps in the permits controller, currently that's manual - do it here or elsewhere?
     *
     * @param array $application application data
     *
     * @return array
     */
    public function __invoke(array $application): array
    {
        //if the application isn't submitted, build the overview as normal
        if ($application['isNotYetSubmitted']){
            $sections = [];
            $appId = $application['id'];

            foreach (self::ROUTE_ORDER as $route => $testedField) {
                $status = $application['sectionCompletion'][$testedField];
                $colour = self::COMPLETION_STATUS_COLOUR[$status];
                array_push($sections, $this->createSection($route, $status, $colour, $appId));
            }

            if (!$application['sectionCompletion']['allCompleted']) {
                foreach (self::CONFIRMATION_ROUTE_ORDER as $route => $testedField) {
                    $status = self::SECTION_COMPLETION_CANNOT_START;
                    $colour = self::COMPLETION_STATUS_COLOUR[$status];
                    array_push($sections, $this->createSection($route, $status, $colour, $appId, false));
                }
            } else{
                foreach (self::CONFIRMATION_ROUTE_ORDER as $route => $testedField) {
                    $status = $application['confirmationSectionCompletion'][$testedField];
                    $colour = self::COMPLETION_STATUS_COLOUR[$status];
                    array_push($sections, $this->createSection($route, $status, $colour, $appId));
                }
            }

            return $sections;
        }

        //application is a status other than not submitted
        return $this->buildReadOnly($application['id']);
    }

    private function buildReadOnly(int $appId): array
    {
        $sections = [];

        foreach (self::ROUTE_ORDER as $route => $testedField) {
            $status = self::SECTION_COMPLETION_CANNOT_START;
            $colour = self::COMPLETION_STATUS_COLOUR[$status];
            array_push($sections, $this->createSection($route, $status, $colour, $appId, false));
        }

        foreach (self::CONFIRMATION_ROUTE_ORDER as $route => $testedField) {
            $status = self::SECTION_COMPLETION_CANNOT_START;
            $colour = self::COMPLETION_STATUS_COLOUR[$status];
            array_push($sections, $this->createSection($route, $status, $colour, $appId, false));
        }

        return $sections;
    }

    /**
     * create a section
     *
     * @param string $route
     * @param string $status
     * @param string $appId
     * @param bool   $enabled
     *
     * @return ViewModel
     */
    private function createSection(string $route, string $status, string $colour, string $appId, bool $enabled = true): ViewModel
    {
        $section = new ViewModel();
        $section->setTemplate('partials/overview_section');
        $section->setVariable('enabled', $enabled);
        $section->setVariable('status', self::COMPLETION_STATUS[$status]);
        $section->setVariable('statusColour', $colour);
        $section->setVariable('identifier', $appId);
        $section->setVariable('identifierIndex', 'id');
        $section->setVariable('name', 'section.name.' . $route);
        $section->setVariable('route', 'permits/' . $route);

        return $section;
    }
}

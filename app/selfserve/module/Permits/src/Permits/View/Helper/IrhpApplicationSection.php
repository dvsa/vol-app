<?php

namespace Permits\View\Helper;

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
    const ROUTE_COUNTRIES = 'permits/application/countries';
    const ROUTE_NO_OF_PERMITS = 'permits/application/no-of-permits';
    const ROUTE_CHECK_ANSWERS = 'permits/application/check-answers';
    const ROUTE_DECLARATION = 'permits/application/declaration';
    const ROUTE_FEE = 'permits/application/fee';
    const ROUTE_SUBMITTED = 'permits/application/submitted';

    const ROUTE_CANCEL_APPLICATION = 'permits/application/cancel';
    const ROUTE_CANCEL_CONFIRMATION = self::ROUTE_CANCEL_APPLICATION . '/confirmation';
    const ROUTE_WITHDRAW_APPLICATION = 'permits/application/withdraw';
    const ROUTE_WITHDRAW_CONFIRMATION = self::ROUTE_WITHDRAW_APPLICATION . '/confirmation';
    const ROUTE_DECLINE_APPLICATION = 'permits/application/decline';
    const ROUTE_DECLINE_CONFIRMATION = self::ROUTE_DECLINE_APPLICATION . '/confirmation';

    /**
     * list of overview routes and the field denoting completion status
     */
    const ROUTE_ORDER = [
        self::ROUTE_LICENCE => 'licence',
        self::ROUTE_COUNTRIES => 'countries',
        self::ROUTE_NO_OF_PERMITS => 'permitsRequired',
        self::ROUTE_CHECK_ANSWERS => 'checkedAnswers',
        self::ROUTE_DECLARATION => 'declaration',
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
     * @param array $application application data
     *
     * @return array
     */
    public function __invoke(array $application): array
    {
        $sections = [];
        $appId = $application['id'];

        foreach (self::ROUTE_ORDER as $route => $field) {
            $sections[] = $this->createSection($route, $application['sectionCompletion'][$field], $appId);
        }

        return $sections;
    }

    /**
     * create a section
     *
     * @param string $route  route
     * @param string $status status
     * @param int    $appId  application id
     *
     * @return ViewModel
     */
    private function createSection(string $route, string $status, int $appId): ViewModel
    {
        $section = new ViewModel();
        $section->setTemplate('partials/overview_section');
        $section->setVariable('enabled', $status !== self::SECTION_COMPLETION_CANNOT_START);
        $section->setVariable('status', self::COMPLETION_STATUS[$status]);
        $section->setVariable('statusColour', self::COMPLETION_STATUS_COLOUR[$status]);
        $section->setVariable('identifier', $appId);
        $section->setVariable('identifierIndex', 'id');
        $section->setVariable('name', 'section.name.' . str_replace('permits/', '', $route));
        $section->setVariable('route', $route);

        return $section;
    }
}

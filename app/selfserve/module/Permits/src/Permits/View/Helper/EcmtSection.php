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

    //cancellation
    const ROUTE_ECMT_CANCEL_APPLICATION = 'ecmt-cancel-application';
    const ROUTE_ECMT_CANCEL_CONFIRMATION = self::ROUTE_ECMT_CANCEL_APPLICATION . '/confirmation';

    const ROUTE_ORDER = [
        self:: ROUTE_ECMT_LICENCE,
        self:: ROUTE_ECMT_EURO6,
        self:: ROUTE_ECMT_CABOTAGE,
        self:: ROUTE_ECMT_COUNTRIES,
        self:: ROUTE_ECMT_NO_OF_PERMITS,
        self:: ROUTE_ECMT_TRIPS,
        self:: ROUTE_ECMT_INTERNATIONAL_JOURNEY,
        self:: ROUTE_ECMT_SECTORS,
        self:: ROUTE_ECMT_CHECK_ANSWERS,
        self:: ROUTE_ECMT_DECLARATION,
    ];

    /**
     * Currently returns an array of sections - could be much better, but this is part 1...
     *
     * @todo enabled on/off, based on info we don't yet know - mostly self explanatory, but what are the rules?
     * @todo work out what to do with completion statuses - do we have the info?
     * @todo investigate (do we need) a separate view partial, currently sharing/reusing view from licence applications
     * @todo section numbering (xx of xx complete) - do it here or elsewhere?
     * @todo handle ordering of steps in the permits controller, currently that's manual - do it here or elsewhere?
     *
     * @param array $application application data
     *
     * @return array
     */
    public function __invoke(array $application): array
    {
        $sections = [];

        foreach (self::ROUTE_ORDER as $route) {
            $section = new ViewModel();
            $section->setTemplate('partials/overview_section');
            $section->setVariable('enabled', true);
            $section->setVariable('status', 'statuses to be added');
            $section->setVariable('statusColour', 'green');
            $section->setVariable('identifier', $application['id']);
            $section->setVariable('identifierIndex', 'id');
            $section->setVariable('name', 'section.name.' . $route);
            $section->setVariable('route', 'permits/' . $route);
            array_push($sections, $section);
        }

        return $sections;
    }
}

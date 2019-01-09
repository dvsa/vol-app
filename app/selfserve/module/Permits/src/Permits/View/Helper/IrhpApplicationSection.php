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
    const ROUTE_COUNTRIES = 'permits/application/countries';
    const ROUTE_NO_OF_PERMITS = 'permits/application/no-of-permits';
    const ROUTE_CHECK_ANSWERS = 'permits/application/check-answers';
    const ROUTE_DECLARATION = 'permits/application/declaration';
    const ROUTE_FEE = 'permits/application/fee';

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
    ];

    /**
     * list of overview routes that are used for confirmation
     */
    const CONFIRMATION_ROUTE_ORDER = [
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

        foreach (self::ROUTE_ORDER as $route => $testedField) {
            $sections[] = $this->createSection($route, $application['sectionCompletion'][$testedField], $appId);
        }

        if (!$application['sectionCompletion']['allCompleted']) {
            return $this->addDisabledConfirmationSections($sections, $appId);
        }

        if (!$application['hasCheckedAnswers']) {
            return $this->checkAnswersNotStarted($sections, $appId);
        }

        return $this->checkAnswersCompleted($sections, $appId, $application['hasMadeDeclaration']);
    }

    /**
     * confirmation sections if check answers is available but hasn't been started
     *
     * @param array $sections existing sections
     * @param int   $appId    app id
     *
     * @return array
     */
    private function checkAnswersNotStarted(array $sections, int $appId): array
    {
        $sections[] = $this->notStartedSection(self::ROUTE_CHECK_ANSWERS, $appId);
        $sections[] = $this->cannotStartSection(self::ROUTE_DECLARATION, $appId);

        return $sections;
    }

    /**
     * confirmation sections if check answers already completed
     *
     * @param array $sections          existing sections
     * @param int   $appId             app id
     * @param bool $hasMadeDeclaration whether declaration has been made
     *
     * @return array
     */
    private function checkAnswersCompleted(array $sections, int $appId, bool $hasMadeDeclaration): array
    {
        $sections[] = $this->completedSection(self::ROUTE_CHECK_ANSWERS, $appId);

        if ($hasMadeDeclaration) {
            $sections[] = $this->completedSection(self::ROUTE_DECLARATION, $appId);

            return $sections;
        }

        $sections[] = $this->notStartedSection(self::ROUTE_DECLARATION, $appId);

        return $sections;
    }

    /**
     * create disabled confirmation sections
     *
     * @param array $sections existing sections
     * @param int   $appId    app id
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function addDisabledConfirmationSections(array $sections, int $appId): array
    {
        foreach (self::CONFIRMATION_ROUTE_ORDER as $route => $testedField) {
            $sections[] = $this->cannotStartSection($route, $appId);
        }

        return $sections;
    }

    /**
     * Create a section that cannot be started
     *
     * @param string $route route
     * @param int    $appId application id
     *
     * @return ViewModel
     */
    private function cannotStartSection(string $route, int $appId): ViewModel
    {
        return $this->createSection($route, self::SECTION_COMPLETION_CANNOT_START, $appId);
    }

    /**
     * Create a section that isn't started but can be
     *
     * @param string $route route
     * @param int    $appId application id
     *
     * @return ViewModel
     */
    private function notStartedSection(string $route, int $appId): ViewModel
    {
        return $this->createSection($route, self::SECTION_COMPLETION_NOT_STARTED, $appId);
    }

    /**
     * Create a completed section
     *
     * @param string $route route
     * @param int    $appId application id
     *
     * @return ViewModel
     */
    private function completedSection(string $route, int $appId): ViewModel
    {
        return $this->createSection($route, self::SECTION_COMPLETION_COMPLETED, $appId);
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

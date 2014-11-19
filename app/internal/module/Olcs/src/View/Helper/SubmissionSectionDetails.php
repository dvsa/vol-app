<?php

namespace Olcs\View\Helper;

use Zend\I18n\Translator\TranslatorInterface as Translator;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\Form\Exception;

/**
 * View helper to render the submission sections
 *
 */
class SubmissionSectionDetails extends AbstractHelper
{
    const DEFAULT_HELPER = 'submissionsectionoverview';

    /**
     * Type map to view helper
     *
     * @var array
     */
    protected $typeMap = array(
        'introduction'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'case-summary'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'case-outline'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'most-serious-infringement'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'persons'   => 'SubmissionSectionTable',
        'operating-centres'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'conditions-and-undertakings'   => 'SubmissionSectionMultipleTables',
        'intelligence-unit-check'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'interim'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'advertisement'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'linked-licences-app-numbers'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'lead-tc-area'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'current-submissions'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'auth-requested-applied-for'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'transport-managers'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'continuous-effective-control'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'fitness-and-repute'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'previous-history'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'bus-reg-app-details'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'transport-authority-comments'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'total-bus-registrations'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'local-licence-history'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'linked-mlh-history'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'registration-details'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'maintenance-tachographs-hours'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'prohibition-history'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'conviction-fpn-offence-history'   => 'SubmissionSectionTable',
        'annual-test-history'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'penalties'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'other-issues'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'te-reports'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'site-plans'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'planning-permission'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'applicants-comments'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'visibility-access-egress-size'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'compliance-complaints'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'environmental-complaints'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'oppositions'   => 'SubmissionSectionTable',
        'financial-information'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'maps'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'waive-fee-late-fee'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'surrender'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'annex'   => 'Olcs\View\Helper\SubmissionSectionOverview'
    );

    /**
     * Default helper name
     *
     * @var string
     */
    protected $defaultHelper = self::DEFAULT_HELPER;

    /**
     * Renders the data for a SubmissionSection details from the provided $submissionSection
     *
     * @param  String $submissionSection
     * @param Array $data
     * @return string
     */
    public function __invoke($submissionSection = '', $data = array())
    {
        if (empty($submissionSection)) {
            return '';
        }
        return $this->render($submissionSection, $data);
    }

    /**
     * Renders the appropriate View Helper for the submissionSection
     *
     * @param String $submissionSection
     * @param Array $data
     *
     * @return mixed
     */
    protected function render($submissionSection, $data)
    {

        $renderer = $this->getView();

        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }

        if (isset($this->typeMap[$submissionSection])) {
            return $this->renderHelper($this->typeMap[$submissionSection], $submissionSection, $data);
        }
        return null;
    }

    /**
     * Render element by helper name
     *
     * @param string $name
     * @param ElementInterface $element
     * @return string
     */
    protected function renderHelper($name, $submissionSection, $data)
    {
        $helper = $this->getView()->plugin($name);
        return $helper($submissionSection, $data);
    }
}
